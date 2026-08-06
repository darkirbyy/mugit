# Specification

The project is divided in two parts, with the idea that each part can be used independently, or run on a different machine (not tested !).  
To achieve that, here is the APIv1 between the CORE and the UI.

## General consideration

- Each line MUST end with '\n'.  
- Exit code MUST be 0 on success, anything between 1 and TODO on error (see [Exit code](#exit-code)).  
- Errors MUST be printed on stderr ; success response MUST be printed on stdout.
- Valid names for the repositories : MUST start with a letter ; MUST only contains letters, digits, symbols '-' and '_'.
- Valid UUIDs for the users : MUST use the standard format 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx' where x is a digit or a letter between 'a' and 'f'.  
- Valid public keys for the users : MUST be a ed25519 keys, without the 'ssh-ed25519' prefix nor the optional comment suffix ; MUST be enclosed with simple quotes.  
- Valid comments for the users : MUST only contains letters, digits, spaces, symbols '-', '_' and '@' ; multiple spaces MUST be reduced to only one by the API.

## Commands

- `help` : print the global help.

### Repo sub-command

- `repo help` : print the repo sub-command help
- `repo list` : list each repository name (without the `git` suffix), with its size (in Kb) on a new line, sorted alphabetically
- `repo create <name>` : create a new repository named `<name>` if not already exists
- `repo rename <old-name> <new-name>` : rename an existing repository named `<old-name>` to `<new-name>` if not already exists
- `repo delete <name>` : delete an existing repository named `<name>`

### User sub-command

- `user help` : print the user sub-command help
- `user list` : list the UUID of each user having at least one key registered on a new line
- `user key-list <uuid>` : list each registered key with its timestamp (in UTC) and potential comment for the user `<uuid>` on a new line
- `user key-add <uuid> <key> [comment]` : register a new key `<key>` for the user `<uuid>`, adding the current timestamp and with an optional comment `[comment]`
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

To ease out the deployement of a new version of the API, the UI defines a `CoreInterface`. For the moment, there is only one implementation using the APIv1 and a SSH root connection between the UI and the CORE.
