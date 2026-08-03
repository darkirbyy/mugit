#!/bin/sh

chown root:root /etc/ssh/ssh_host_ed25519_key /etc/ssh/ssh_host_ed25519_key.pub /root/.ssh/authorized_keys
chown -R git:git /home/git/git-shell-commands

echo "AllowUsers root@$UI_ADDR" > /etc/ssh/sshd_config.d/90_allow_root.conf 

exec /usr/sbin/sshd -D -e "$@"