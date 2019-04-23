#!/share/bin/python

from optparse import OptionParser
import ConfigParser, os, sys
from binascii import hexlify, unhexlify
from simplecrypt import encrypt, decrypt
import urllib, json
import re
import cgi

def callApi(url, file):
    response = urllib.urlopen(url);
    data = json.loads(response.read())
    print data
    text_file = open(file, "a")
    text_file.write(data)
    text_file.close()
    return data

 
def getBasePath():
    config = ConfigParser.ConfigParser()
    config.readfp(open('../config/.sec'))
    basePath = config.get('CONFIG', 'API_PATH')
    return basePath


def main():
    basePath=getBasePath()
    url = basePath + "/api/service.php?upd=updateAmzInst"
    resAmz=callApi(url, "../public/tmp/api/updateAmzInst.txt")
    url = basePath + "/api/service.php?upd=updateRunStat"
    resRun=callApi(url, "../public/tmp/api/updateRunStat.txt")

if __name__ == "__main__":
    main()
