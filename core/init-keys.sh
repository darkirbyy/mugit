#!/bin/sh
cd "$(dirname "$0")"

# Checking if the first argument is present, and valid
if [ -z "$1" ] || [ ! -d "$1" ]; then
    echo "The first argument must be the path to the keys directory,"
    echo "relative to this script location. It must already exist."
    exit 1
fi

# Checking if the keys are already present
if [ -e "$1/ssh_host_ed25519_key" ] || [ -e "$1/ssh_root_ed25519_key" ]; then
    echo "The keys have already been generated, aborting."
    exit 1
fi

# Generating the server keys and a root keys access
ssh-keygen -q -t ed25519 -f "$1/ssh_host_ed25519_key" -N '' -C '' 
ssh-keygen -q -t ed25519 -f "$1/ssh_root_ed25519_key" -N '' -C '' 

# Printing the variables for the UI .env.local/.env.test.local file
host_pubkey=$(cat "$1/ssh_host_ed25519_key.pub" | xargs)
root_prikey=$(cat "$1/ssh_root_ed25519_key")
echo "############################################"
echo "### VARIABLES ADDED BY init-keys.sh ########"
echo "############################################"
echo "CORE_HOST_PUBKEY=\"$host_pubkey\""
echo "CORE_ROOT_PRIKEY=\"$root_prikey\""

# Removing the root private key
rm "$1/ssh_root_ed25519_key"

exit 0