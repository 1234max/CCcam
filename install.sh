#!/bin/bash
git clone https://github.com/1234max/CCcam.git
tar xzvf CCcam.tar.gz
cp CCcam.x86 /usr/local/bin/
mkdir /var/etc
mkdir /var/keys
mkdir /var/log/CCcam
touch /var/run/CCcam.pid
