#!/share/bin/python

from optparse import OptionParser
import ConfigParser, os, argparse


def getConf():
    ret = dict();  
    config = ConfigParser.ConfigParser()
    config.readfp(open('../config/.sec'))
    ret['DB']     = config.get('Dolphinnext', 'DB')
    ret['DBUSER'] = config.get('Dolphinnext', 'DBUSER')
    ret['DBPASS'] = config.get('Dolphinnext', 'DBPASS')
    return ret


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--version", type=str, help="Please enter the version or branch name to pull", default="master")
    args = parser.parse_args()
    conf=getConf()
    ownerID = "1"
    pull_cmd = "cd .. && git pull origin " + args.version + " 2>&1"
    print "INFO: Pulling"+ "\nRUN : "+ pull_cmd
    pull_cmd_log = os.popen(pull_cmd).read()
    print "\n" + "LOG :\n" + pull_cmd_log
    runUpdateCmd = "cd ../db/ && bash ./runUpdate " + conf['DB'] + " " + ownerID + " " + conf['DBUSER'] + " " + conf['DBPASS'] + " 2>&1"
    runUpdateCmdLog = "cd ../db/ && bash ./runUpdate " + conf['DB'] + " " + ownerID + " " + conf['DBUSER'] + " " + "*****" + " 2>&1"
    print "\nINFO:" + " Database Update\nRUN : " + runUpdateCmdLog
    runUpdateCmd_log = os.popen(runUpdateCmd).read()
    print "LOG :\n" + runUpdateCmd_log
    
    
if __name__ == "__main__":
    main()




