#!/share/bin/python

import json
import urllib
from datetime import datetime
from simplecrypt import encrypt, decrypt
from binascii import hexlify, unhexlify
import cgi
import re
from optparse import OptionParser
import ConfigParser
import os
import sys
os.environ['http_proxy'] = ''


def callApi(url):
    response = urllib.urlopen(url)
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
    basePath = getBasePath()
    token = getToken()
    url = basePath + "/api/service.php?upd=updateCloudInst&token=" + token
    print url
    resAmz = callApi(url)
    url = basePath + "/api/service.php?upd=updateRunStat&token=" + token
    print url
    resRun = callApi(url)
    url = basePath + "/api/service.php?upd=submitCronJobs&token=" + token
    print url
    resCron = callApi(url)
    print resCron
    url = basePath + "/api/service.php?upd=cleanTempDir&token=" + token
    print url
    resClean = callApi(url)


if __name__ == "__main__":
    main()
