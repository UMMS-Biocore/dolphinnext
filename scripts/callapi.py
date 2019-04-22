#!/share/bin/python

from optparse import OptionParser
import ConfigParser, os, sys
from binascii import hexlify, unhexlify
from simplecrypt import encrypt, decrypt
import urllib, json
import re
import cgi

def callApi(url):
    response = urllib.urlopen(url);
    data = json.loads(response.read())
    print data
    return data

 
def getBasePath():
    config = ConfigParser.ConfigParser()
    config.readfp(open('../config/.sec'))
    basePath = config.get('CONFIG', 'BASE_PATH')
    return basePath


def main():
    basePath=getBasePath()
    print basePath
    url = basePath + "/api/service.php?upd=updateAmzInst"
    results=callApi(url)
    url = basePath + "/api/service.php?upd=updateStatus"
    results=callApi(url)
    print url

if __name__ == "__main__":
    main()
