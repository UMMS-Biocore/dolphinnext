#!/usr/bin/python

import json
# import urllib
import urllib.request
from datetime import datetime
from simplecrypt import encrypt, decrypt
from binascii import hexlify, unhexlify
import cgi
import re
from optparse import OptionParser
from six.moves import configparser
import os
import sys
os.environ['http_proxy'] = ''


def callApi(url):
    response = urllib.request.urlopen(url)
    data = json.loads(response.read())
    print(data)
    return data


def getBasePath():
    config = configparser.ConfigParser()
    config.read_file(open('../config/.sec'))
    basePath = config.get('CONFIG', 'API_URL')
    return basePath


def getToken():
    config = configparser.ConfigParser()
    config.read_file(open('../config/.sec'))
    password = config.get('Dolphinnext', 'VERIFY')
    encrypted = encrypt(password, 'OK').hex()

    return encrypted


def main():
    basePath = getBasePath()
    token = getToken()
    url = basePath + "/api/service.php?upd=updateCloudInst&token=" + token
    print(url)
    resAmz = callApi(url)
    url = basePath + "/api/service.php?upd=updateRunStat&token=" + token
    print(url)
    resRun = callApi(url)
    url = basePath + "/api/service.php?upd=submitCronJobs&token=" + token
    print(url)
    resCron = callApi(url)
    print(resCron)
    url = basePath + "/api/service.php?upd=cleanTempDir&token=" + token
    print(url)
    resClean = callApi(url)


if __name__ == "__main__":
    main()
