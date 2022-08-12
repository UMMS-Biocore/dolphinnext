#!/usr/bin/python

from six.moves import configparser
import os
import sys
from binascii import hexlify, unhexlify
from simplecrypt import encrypt, decrypt

salt_type = sys.argv[1]
item = sys.argv[2]

config = configparser.ConfigParser()
config.read_file(open('../config/.sec'))
password = config.get('Dolphinnext', salt_type)
encrypted = encrypt(password, item).hex()
print(encrypted)
