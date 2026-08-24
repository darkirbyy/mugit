# Specification

The project is divided in two parts, with the idea that each part can be used independently, or run on a different machine (not tested !).  
To achieve that, here is the API between the CORE and the UI.

## General consideration

- Each line MUST end with '\n'.  
- Exit code MUST be 0 on success, anything between 1 and 10 on error (see [Exit code](#exit-code)).  
- Errors MUST be printed on stderr ; success response MUST be printed on stdout.
- Valid names for the repositories: MUST start with a letter ; MUST only contains letters, digits and the symbols '-' or '_' ; MUST be maximum 128 characters.
- Valid uuids for the users: MUST use the standard format 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx' where x is a digit or a letter between 'a' and 'f'.  
- Valid public keys for the users: MUST be generated with the ed25519 algorithm, without the 'ssh-ed25519' prefix nor the optional comment suffix ; MUST be enclosed in simple quotes ; MUST be extacly 68 characters.  
- Valid comments for the users: MUST only contains letters, digits, spaces and symbols '-', '_' or '@' ; multiple spaces MUST be reduced to only one by the API ; MUST be maximum 255 characters.

## Commands

- `help` : print the global help.

### Repo sub-command

- `repo help` : print the repo sub-command help
- `repo list` : list all repositories names (without the `git` suffix) and sizes (in Kio), sorted alphabetically
- `repo create <name>` : create a new repository named `<name>` if not already exists
- `repo rename <old-name> <new-name>` : rename an existing repository named `<old-name>` to `<new-name>` if not already exists
- `repo delete <name>` : delete an existing repository named `<name>`

### User sub-command

- `user help` : print the user sub-command help
- `user list` : list alls uuids of users having at least one key registered
- `user key-list <uuid>` : list all registered keys with its timestamp (in UTC) and potential comment for the user `<uuid>`
- `user key-add <uuid> <key> [comment]` : register a new key `<key>` for the user `<uuid>` with an optional comment `[comment]`
- `user key-remove <uuid> <key>` : remove an existing key `<key>` for the user `<uuid>`
- `user delete <uuid>` : delete all registered keys of the user `<uuid>`

## Exit code

- `0` : success
- `1` : missing (sub-)command
- `2` : unknown (sub-)command
- `3` : missing argument(s)
- `4` : invalid argument(s)
- `5` : invalid options(s)
- `6` : name does not exist
- `7` : name already exists
- `8` : key does not exist
- `9` : key already exists
- `10` : other error

## PHP Interface

To ease out the deployement of a new version of the API or a new communication layer, the UI defines a `CoreInteractInterface` and a `CoreExecInterface`. For the moment, there is only one implementation using the API and a SSH root connection.
