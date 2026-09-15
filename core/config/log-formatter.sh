#!/bin/sh

while IFS= read -r line; do
    echo "$(date '+%Y-%m-%dT%H:%M:%S%z') $line" >> /var/log/mugit/sshd.log
done