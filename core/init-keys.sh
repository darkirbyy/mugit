#!/bin/sh

# Checking if the keys folder already exists
if [ -d "./keys" ]; then
    echo "The 'keys' folder already exists, aborting."
    exit 1
fi

# Generating the server keys and a root keys access
mkdir keys
ssh-keygen -t ed25519 -f keys/ssh_host_ed25519_key -N '' -C '' 
ssh-keygen -t ed25519 -f keys/ssh_root_ed25519_key -N '' -C '' 

# Printing the variables for the UI .env.local file
echo "############################################"
echo "### VARIABLES ADDED BY init-keys.sh ########"
echo "############################################"
host_pubkey=$(cat keys/ssh_host_ed25519_key.pub)
root_prikey=$(cat keys/ssh_root_ed25519_key)
echo "CORE_HOST_PUBKEY=\"$host_pubkey\""
echo "CORE_ROOT_PRIKEY=\"$root_prikey\""

# Removing the root private key
rm keys/ssh_root_ed25519_key

exit 0