#!/bin/sh
cd "$(dirname "$0")"

# Checking if the first argument is present, and valid
if [ -z "$1" ] || [ ! -d "$1" ]; then
    echo "The first argument must be the path to the keys directory,"
    echo "relative to this script location. It must already exist."
    exit 1
fi

# Checking if the keys are already present
if [ -e "$1/ssh_host_ed25519_key" ]; then
    echo "The keys have already been generated, skipping."
    exit 0
fi

# Generating the server keys
ssh-keygen -q -t ed25519 -f "$1/ssh_host_ed25519_key" -N '' -C '' 

exit 0