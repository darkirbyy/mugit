#!/bin/sh

read -r uuid args

export USER_UUID="$uuid"
output=$(eval "/root/api.sh $args 2>&1")

echo "$?"
echo "$output"