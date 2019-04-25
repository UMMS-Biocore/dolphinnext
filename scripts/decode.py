import ConfigParser, os, sys
from binascii import hexlify, unhexlify
from simplecrypt import encrypt, decrypt

salt_type = sys.argv[1]
item = sys.argv[2]

config = ConfigParser.ConfigParser()
config.readfp(open('../config/.sec'))
password = config.get('Dolphinnext', salt_type)

decrypted = decrypt(password, unhexlify(item))
print decrypted
