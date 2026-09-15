#!/bin/sh

chown root:root /etc/ssh/ssh_host_ed25519_key /etc/ssh/ssh_host_ed25519_key.pub
chmod 600 /etc/ssh/ssh_host_ed25519_key
chown -R git:git /home/git/git-shell-commands

echo "REPO_MAX_SIZE_MIO=$REPO_MAX_SIZE_MIO" > /etc/git/hooks.conf

touch /var/log/mugit/sshd.log && chown git:git /var/log/mugit/sshd.log

socat TCP4-LISTEN:$SOCKET_PORT,reuseaddr,fork EXEC:/root/socket.sh &
exec /usr/sbin/sshd -D -e "$@" > >(/log-formatter.sh) 2>&1