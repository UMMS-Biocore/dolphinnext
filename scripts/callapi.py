#!/share/bin/python

from optparse import OptionParser
import ConfigParser, os, sys
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
    basePath = config.get('CONFIG', 'API_URL')
    return basePath


def main():
    basePath=getBasePath()
    url = basePath + "/api/service.php?upd=updateAmzInst"
    print url
    resAmz=callApi(url)
    url = basePath + "/api/service.php?upd=updateRunStat"
    print url
    resRun=callApi(url)

if __name__ == "__main__":
    main()
