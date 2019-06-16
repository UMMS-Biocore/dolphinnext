#!/share/bin/python

from optparse import OptionParser
import ConfigParser, os, sys
os.environ['http_proxy']=''
import urllib, json
import re
import cgi
from binascii import hexlify, unhexlify
from simplecrypt import encrypt, decrypt
from datetime import datetime



def callApi(url):
    response = urllib.urlopen(url);
    data = json.loads(response.read())
    print data
    return data


def getBasePath():
    config = ConfigParser.ConfigParser()
    config.readfp(open('../config/.sec'))
    basePath = config.get('CONFIG', 'API_URL')
    return basePath



def getToken():
    config = ConfigParser.ConfigParser()
    config.readfp(open('../config/.sec'))
    password = config.get('Dolphinnext', 'VERIFY')
    encrypted = hexlify(encrypt(password, 'OK'))
    return encrypted


def main():
    basePath=getBasePath()
    token=getToken()
    url = basePath + "/api/service.php?upd=updateAmzInst&token=" + token
    print url
    resAmz=callApi(url)
    url = basePath + "/api/service.php?upd=updateRunStat&token=" + token
    print url
    resRun=callApi(url)
    url = basePath + "/api/service.php?upd=cleanTempDir&token=" + token
    print url
    resClean=callApi(url)

if __name__ == "__main__":
    main()
