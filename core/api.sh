#!/bin/sh

# Define local const
GIT_UID=$(id -u git)
GIT_GID=$(id -g git)
GIT_DIR="/home/git"
SSH_FILE="$GIT_DIR/.ssh/authorized_keys"
NAME_REGEX="^[a-zA-Z]([a-zA-Z0-9_-])*$"
NAME_HELP="The names must start by a letter, and can only use letters, digits, and the symbols '-' or '_'."
UUID_REGEX="^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$"
UUID_HELP="The uuids must be written in standard format : 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'."
KEY_REGEX="^[a-zA-Z0-9/+=\\]+$"
KEY_HELP="The keys must be generated with the ed25519 algorithm. To be parsed correctly, they must given 
  without the 'ssh-ed25519' prefix nor the optional comment suffix and enclosed into simple quote."
COMMENT_REGEX="^[a-zA-Z0-9_@ -]*$"
COMMENT_HELP="The comments can only use letters, digits, spaces and the symbols '-', '_', '@'."

# Define common function
# Syntax : check_command "$command" "$values" "$type" "$help"
check_command(){
    if [ -e $1 ]; then
        >&2 echo "Missing $3. $4" 
        exit 1
    fi

    if echo $2 | grep -vq "$1"; then
        >&2 echo "Unkown $3 '$1'. $4" 
        exit 2
    fi
}

# Syntax : check_argument "$argument" "$regex" "$usage" "$help"
check_argument(){
    if [ -e $1 ]; then
        >&2 echo "Missing argument(s). $3" 
        exit 3
    fi

    if echo $1 | grep -vqE "$2"; then
        >&2 echo "Invalid argument(s). $4" 
        exit 4
    fi
}

# Syntax : check_option "$argument" "$regex" "$help"
check_option(){
    if echo $1 | grep -vqE "$2"; then
        >&2 echo "Invalid option(s). $3" 
        exit 5
    fi
}

# Syntax : check_path "$path" "$inverted" "$help"
check_path(){
    if [ $2 -d $1 ]; then
        >&2 echo "$3" 
        exit $([ "$2" = "!" ] && echo "6" || echo "7")
    fi
}

# Syntax : check_key "$uuid" "$key" "$inverted" "$help"
check_key(){
    if [ $3 $(grep -F "$2 $1:" "$SSH_FILE" | wc -l) = 0 ]; then
        >&2 echo "$4" 
        exit $([ "$3" = "!" ] && echo "8" || echo "9")
    fi
}

# Syntax : report_error "$error" "$message"
report_error(){
    >&2 echo "$2 Error :" 
    >&2 echo "$1" 
    exit 10
}

command=$1
check_command "$command" "help,repo,user" "command" "Type 'help' to print list of available commands."

if   [ $command = "help" ]; then
    echo "Available commands:"
    echo "  help       print this help"
    echo "  repo       manage repositories"
    echo "  user       manage users and their public SSH keys"
	exit 0
elif [ $command = "repo" ]; then
	shift

    subcommand=$1
    check_command "$subcommand" "help,list,create,rename,delete" "sub-command" "Type 'repo help' to print the list of available sub-commands."

    if [ $subcommand = "help" ]; then
        echo "Manage repositories. Available sub-commands:"
        echo "  repo help                               print this help"
        echo "  repo list                               list all existing repositories and their size in Kio without '.git' suffix and sorted alphabetically"
        echo "  repo create <name>                      create a new repository if not already exists"
        echo "  repo rename <old-name> <new-name>       rename an existing repository"
        echo "  repo delete <name>                      delete an existing repository"
        echo ""
        echo "$NAME_HELP"
        exit 0
    fi

    if [ $subcommand = "list" ]; then
        output=$(du -d 1 $GIT_DIR | grep -E '\.git$' | sed -E 's#([0-9]+).*/([a-zA-Z0-9_-]+)\.git#\2 \1#g' | sort 2>&1)
        if [ $? = 0 ]; then
            echo "$output"
            exit 0
        else
            report_error "$output" "Failed to list the repositories."
        fi
        exit 0
    elif [ $subcommand = "create" ]; then
        shift

        name=$1
        check_argument "$name" "$NAME_REGEX" "Usage is 'repo create <name>'" "$NAME_HELP"

        path="$GIT_DIR/$name.git"
        check_path "$path" "" "The name '$name' is already used by an existing repository."

        uid=$(id -u git)
        gid=$(id -g git)
        output=$(git init --bare $path 2>&1 && chown -R $GIT_UID:$GIT_GID $path 2>&1)
        if [ $? = 0 ]; then
            echo "Created empty repository '$name'."
            exit 0
        else
           report_error "$output" "Failed to create the repository."
        fi
    elif [ $subcommand = "rename" ]; then
        shift

        oldname=$1
        check_argument "$oldname" "$NAME_REGEX" "Usage is 'repo rename <old-name> <new-name>'" "$NAME_HELP"
        newname=$2
        check_argument "$newname" "$NAME_REGEX" "Usage is 'repo rename <old-name> <new-name>'" "$NAME_HELP"

        oldpath="$GIT_DIR/$oldname.git"
        check_path "$oldpath" "!" "The old name '$oldname' does not correspond to an existing repository."
        newpath="$GIT_DIR/$newname.git"
        check_path "$newpath" "" "The new name '$newname' is already used by an existing repository."

        output=$(mv -f "$oldpath" "$newpath" 2>&1)
        if [ $? = 0 ]; then
            echo "Renamed repository from '$oldname' to '$newname'."
            exit 0
        else
            report_error "$output" "Failed to rename the repository."
        fi
    elif [ $subcommand = "delete" ]; then
        shift

        name=$1
        check_argument "$name" "$NAME_REGEX" "Usage is 'repo delete <name>'" "$NAME_HELP"

        path="$GIT_DIR/$name.git"
        check_path "$path" "!" "The name '$name' does not correspond to an existing repository."

        output=$(rm -rf "$path" 2>&1)
        if [ $? = 0 ]; then
            echo "Deleted repository '$name'."
            exit 0
        else
            report_error "$output" "Failed to delete the repository."
        fi
    fi
elif [ $command = "user" ]; then
	shift

    if [ ! -f $SSH_FILE ]; then
        ssh_dir=$(dirname "$SSH_FILE")
        mkdir -p "$ssh_dir"
        touch "$SSH_FILE"
        chown -R $GIT_UID:$GIT_GID "$ssh_dir"
    fi

    subcommand=$1
    check_command "$subcommand" "help,list,key-list,key-add,key-remove,delete" "sub-command" "Type 'user help' to print the list of available sub-commands."

    if [ $subcommand = "help" ]; then
        echo "Manage users and their public SSH keys. Available sub-commands:"
        echo "  user help                               print this help"
        echo "  user list                               list all user uuids having at least one registered key"
        echo "  user key-list <uuid>                    list all registered keys and their timestamp for the given user"
        echo "  user key-add <uuid> <key> [comment]     register a new key for the given user, with an optional comment"
        echo "  user key-remove <uuid> <key>            remove an existing key for the given user"
        echo "  user delete <uuid>                      delete all keys for the given user, cutting effectively all its access"
        echo ""
        echo "$UUID_HELP"
        echo "$KEY_HELP"
        echo "$COMMENT_HELP"
        exit 0
    elif [ $subcommand = "list" ]; then
        output=$(cat "$SSH_FILE" | cut -d' ' -f3 | cut -d':' -f1 | uniq 2>&1)
        if [ $? = 0 ]; then
            echo "$output"
            exit 0
        else
            report_error "$output" "Failed to list the users."
        fi
    elif [ $subcommand = "key-list" ]; then
        shift
        uuid=$1
        check_argument "$uuid" "$UUID_REGEX" "Usage is 'user key-list <uuid>'" "$UUID_HELP"
        
        key_regex_adapt=$(echo "$KEY_REGEX" | tr -d '^$')
        output=$(cat "$SSH_FILE" | grep -E "ssh-ed25519 $key_regex_adapt $uuid:" | cut -d' ' -f2- | sed -E "s#$uuid:##" | tr ':' ' ' 2>&1)
        if [ $? = 0 ] || [ $? = 1 ]; then
            echo "$output"
            exit 0
        else
            report_error "$output" "Failed to list the keys."
        fi
    elif [ $subcommand = "key-add" ]; then
        shift
        uuid=$1
        check_argument "$uuid" "$UUID_REGEX" "Usage is 'user key-add <uuid> <key> [comment]'" "$UUID_HELP"
        key=$2
        check_argument "$key" "$KEY_REGEX" "Usage is 'user key-add <uuid> <key> [comment]'" "$KEY_HELP"
        check_key "$uuid" "$key" "!" "The key '$key' already exists for the user '$uuid'."
        comment=$(echo $3 | tr -s ' ')
        check_option "$comment" "$COMMENT_REGEX" "$COMMENT_HELP"
        timestamp=$(date +%s)

        output=$(echo "ssh-ed25519 $key $uuid:$timestamp:$comment" >> "$SSH_FILE" 2>&1)
         if [ $? = 0 ]; then
            echo "Added key '$key' for the user '$uuid'."
            exit 0
        else
            report_error "$output" "Failed to add the key."
        fi
        exit 0
    elif [ $subcommand = "key-remove" ]; then
        shift
        uuid=$1
        check_argument "$uuid" "$UUID_REGEX" "Usage is 'user key-remove <uuid> <key>'" "$UUID_HELP"
        key=$2
        check_argument "$key" "$KEY_REGEX" "Usage is 'user key-remove <uuid> <key>'" "$KEY_HELP"
        check_key "$uuid" "$key" "" "The key '$key' does not exist for the user '$uuid'."

        output=$(sed -i "\#^ssh-ed25519 $key $uuid\:#d" "$SSH_FILE" 2>&1)
         if [ $? = 0 ]; then
            echo "Deleted key '$key' for the user '$uuid'."
            exit 0
        else
            report_error "$output" "Failed to delete the key."
        fi
        exit 0
    elif [ $subcommand = "delete" ]; then
        shift
        uuid=$1
        check_argument "$uuid" "$UUID_REGEX" "Usage is 'user delete <uuid>'" "$UUID_HELP"

        key_regex_adapt=$(echo "$KEY_REGEX" | tr -d '^$')
        output=$(sed -i -E "\#^ssh-ed25519 $key_regex_adapt $uuid\:#d" "$SSH_FILE" 2>&1)
         if [ $? = 0 ]; then
            echo "Deleted all keys for the user '$uuid'."
            exit 0
        else
            report_error "$output" "Failed to delete the keys."
        fi
        exit 0
    fi
fi
