#!/bin/sh

chown root:root /etc/ssh/ssh_host_ed25519_key /etc/ssh/ssh_host_ed25519_key.pub /root/.ssh/authorized_keys
chmod 600 /etc/ssh/ssh_host_ed25519_key
chown -R git:git /home/git/git-shell-commands

echo "AllowUsers root@$UI_ADDR" > /etc/ssh/sshd_config.d/90_allow_root.conf 
echo "REPO_MAX_SIZE_MO=$REPO_MAX_SIZE_MO" > /etc/git/hooks.conf

exec /usr/sbin/sshd -D -e "$@"