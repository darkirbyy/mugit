#!/bin/sh

# Define local const
GIT_UID=$(id -u git)
GIT_GID=$(id -g git)
GIT_DIR="/home/git"
SSH_FILE="$GIT_DIR/.ssh/authorized_keys"
LOG_FILE="$GIT_DIR/logs"
NAME_REGEX="^[a-zA-Z]([a-zA-Z0-9_-]){1,127}$"
NAME_HELP="The names must start with a letter, and can only use letters, digits, and the symbols '-' or '_', with 128 characters maximum."
UUID_REGEX="^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$"
UUID_HELP="The uuids must use the standard format 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx' where x is a digit or a letter between 'a' and 'f'."
KEY_REGEX="^[a-zA-Z0-9/+=\\]{68}$"
KEY_HELP="The keys must be generated with the ed25519 algorithm. To be parsed correctly, they must given 
  without the 'ssh-ed25519' prefix nor the optional comment suffix and enclosed in simple quote, with 68 characters exactly."
LOG_OFFSET_REGEX="^(|[1-9][0-9]{0,15}|10000000000000000)$"
LOG_LENGTH_REGEX="^(|[1-9][0-9]{0,3}|10000)$"
LOG_HELP="The offset must be between 1 and 10^16 (default to 1). The length must be between 1 and 10^4 (default to 100)."
COMMENT_REGEX="^[a-zA-Z0-9_@ -]{0,255}$"
COMMENT_HELP="The comments can only use letters, digits, spaces and the symbols '-', '_' or '@', with 255 characters maximum."

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

# Syntax : check_option "$option" "$regex" "$help"
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
        exit $([ "$3" = "!" ] && echo "9" || echo "8")
    fi
}

# Syntax : handle_output "$output" "$success" "$failure" "$log"
handle_output(){
    if [ $? = 0 ]; then
        [ -z "$2" ] && echo "$1" || echo "$2"
        if [ "$4" = "true" ] && echo "$USER_UUID" | grep -qE "$UUID_REGEX"; then
            echo "$(date +%s) $USER_UUID $full_command" >> "$LOG_FILE"  
        fi
        exit 0
    else
        >&2 echo "$3 Error:" 
        >&2 echo "$1" 
        exit 10
    fi
}

full_command="$@"
command=$1
check_command "$command" "help,repo,user,log" "command" "Type 'help' to print list of available commands."

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
        echo " - repo help                               print this help"
        echo " - repo list                               list all repositories names (without the git suffix) and sizes (in Kio), sorted alphabetically"
        echo " - repo create <name>                      create a new repository named <name> if not already exists"
        echo " - repo rename <old-name> <new-name>       rename an existing repository named <old-name> to <new-name> if not already exists"
        echo " - repo delete <name>                      delete an existing repository named <name>"
        echo ""
        echo "$NAME_HELP"
        exit 0
    elif [ $subcommand = "list" ]; then
        output=$(du -d 1 $GIT_DIR | grep -E '\.git$' | sed -E 's#([0-9]+).*/([a-zA-Z0-9_-]+)\.git#\2 \1#g' | sort 2>&1)
        handle_output "$output" ""  "Failed to list the repositories." "false"
    elif [ $subcommand = "create" ]; then
        shift

        name=$1
        check_argument "$name" "$NAME_REGEX" "Usage is 'repo create <name>'" "$NAME_HELP"

        path="$GIT_DIR/$name.git"
        check_path "$path" "" "The name '$name' is already used by an existing repository."

        uid=$(id -u git)
        gid=$(id -g git)
        output=$(git init --bare $path 2>&1 && chown -R $GIT_UID:$GIT_GID $path 2>&1)
        handle_output "$output" "Created empty repository '$name'." "Failed to create the repository." "true"
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
        handle_output "$output" "Renamed repository from '$oldname' to '$newname'." "Failed to rename the repository." "true"
    elif [ $subcommand = "delete" ]; then
        shift

        name=$1
        check_argument "$name" "$NAME_REGEX" "Usage is 'repo delete <name>'" "$NAME_HELP"

        path="$GIT_DIR/$name.git"
        check_path "$path" "!" "The name '$name' does not correspond to an existing repository."

        output=$(rm -rf "$path" 2>&1)
        handle_output "$output" "Deleted repository '$name'." "Failed to delete the repository." "true"
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
        echo " - user help                               print this help"
        echo " - user list                               list alls uuids of users having at least one key registered"
        echo " - user key-list <uuid>                    list all registered keys, timestamps (in UTC) and potential comments for the user <uuid>"
        echo " - user key-add <uuid> <key> [comment]     register a new key <key> for the user <uuid> with an optional comment [comment]"
        echo " - user key-remove <uuid> <key>            remove an existing key <key> for the user <uuid>"
        echo " - user delete <uuid>                      delete all registered keys of the user <uuid>"
        echo ""
        echo "$UUID_HELP"
        echo "$KEY_HELP"
        echo "$COMMENT_HELP"
        exit 0
    elif [ $subcommand = "list" ]; then
        output=$(cat "$SSH_FILE" | cut -d' ' -f3 | cut -d':' -f1 | sort | uniq 2>&1)
        handle_output "$output" "" "Failed to list the users." "false"
    elif [ $subcommand = "key-list" ]; then
        shift
        uuid=$1
        check_argument "$uuid" "$UUID_REGEX" "Usage is 'user key-list <uuid>'" "$UUID_HELP"
        
        key_regex_adapt=$(echo "$KEY_REGEX" | tr -d '^$')
        output=$(cat "$SSH_FILE" | grep -E "ssh-ed25519 $key_regex_adapt $uuid:" | cut -d' ' -f2- | sed -E "s#$uuid:##" | tr ':' ' ' 2>&1)
        handle_output "$output" "" "Failed to list the keys." "false"
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
        handle_output "$output" "Added key '$key' for the user '$uuid'." "Failed to add the key." "true"
    elif [ $subcommand = "key-remove" ]; then
        shift
        uuid=$1
        check_argument "$uuid" "$UUID_REGEX" "Usage is 'user key-remove <uuid> <key>'" "$UUID_HELP"
        key=$2
        check_argument "$key" "$KEY_REGEX" "Usage is 'user key-remove <uuid> <key>'" "$KEY_HELP"
        check_key "$uuid" "$key" "" "The key '$key' does not exist for the user '$uuid'."

        output=$(sed -i "\#^ssh-ed25519 $key $uuid\:#d" "$SSH_FILE" 2>&1)
        handle_output "$output" "Removed key '$key' for the user '$uuid'." "Failed to remove the key." "true"
    elif [ $subcommand = "delete" ]; then
        shift
        uuid=$1
        check_argument "$uuid" "$UUID_REGEX" "Usage is 'user delete <uuid>'" "$UUID_HELP"

        key_regex_adapt=$(echo "$KEY_REGEX" | tr -d '^$')
        output=$(sed -i -E "\#^ssh-ed25519 $key_regex_adapt $uuid\:#d" "$SSH_FILE" 2>&1)
        handle_output "$output" "Deleted all keys for the user '$uuid'." "Failed to delete the keys." "true"
    fi
elif [ $command = "log" ]; then
	shift

    if [ ! -e "$LOG_FILE" ]; then
        touch "$LOG_FILE"
        chown $GIT_UID:$GIT_GID "$LOG_FILE"
    fi

    subcommand=$1
    check_command "$subcommand" "help,size,list,purge" "sub-command" "Type 'log help' to print the list of available sub-commands."

    if [ $subcommand = "help" ]; then
        echo "Manage logs. Available sub-commands:"
        echo " - log help                      print this help"
        echo " - log size                      print the size of the logs file"
        echo " - log list [offset] [length]    list [length] logs starting at [offset], with timestamp, user uuid and command executed"
        echo " - log purge                     purge all the logs"
        echo ""
        echo "$LOG_HELP"
        exit 0
    elif [ $subcommand = "size" ]; then
        # todo : redirect error ?
        output=$(wc -l "$LOG_FILE" | cut -d' ' -f1 2>&1)
        handle_output "$output" "" "Failed to count the logs size." "false"
    elif [ $subcommand = "list" ]; then
        shift
        offset=$1
        check_option "$offset" "$LOG_OFFSET_REGEX" "$LOG_HELP"
        shift
        length=$1
        check_option "$length" "$LOG_LENGTH_REGEX" "$LOG_HELP"

        [ -z $offset ] && offset=1
        [ -z $length ] && length=50
        end=$(($offset + $length - 1))

        output=$(sed -n "$offset,$end"p "$LOG_FILE" 2>&1)
        handle_output "$output" "" "Failed to list the logs." "false"
    elif [ $subcommand = "purge" ]; then
        # todo : redirect error ?
        output=$(echo -n > "$LOG_FILE")
        handle_output "$output" "Purged all logs." "Failed to purge the logs." "false"
    fi
fi
