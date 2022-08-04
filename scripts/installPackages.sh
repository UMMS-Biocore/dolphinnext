#!/bin/bash
set -e 

if ! [ -x "$(command -v aws)" ]; then
  echo 'INFO: AWS CLI is not installed.' >&2
  cd ~ 
  curl "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip"
  unzip awscliv2.zip
  sudo ./aws/install
fi
