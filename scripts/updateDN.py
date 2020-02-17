#!/share/bin/python

from optparse import OptionParser
import os, argparse, mysql.connector
try:
    import configparser
except:
    from six.moves import configparser


scriptDir = os.path.dirname(os.path.realpath(__file__))

    
def getConf():
    ret = dict();  
    config = configparser.ConfigParser()
    try:
        config.readfp(open(scriptDir+'/../config/.sec'))
        ret['DB']     = config.get('Dolphinnext', 'DB')
        ret['DBUSER'] = config.get('Dolphinnext', 'DBUSER')
        ret['DBPASS'] = config.get('Dolphinnext', 'DBPASS')
        ret['DBHOST'] = config.get('Dolphinnext', 'DBHOST')
        ret['DBPORT'] = config.get('Dolphinnext', 'DBPORT')
    except:
        #When .sec file is not found (eg. docker tests), use following defaults:     
        ret['DB']     = 'dolphinnext'
        ret['DBUSER'] = 'root'
        ret['DBPASS'] = ''
        ret['DBHOST'] = 'localhost'
        ret['DBPORT'] = '3306'
    return ret

def executeScriptsFromFile(filename, cursor):
    log = ""
    fd = open(filename, 'r')
    sqlFile = fd.read()
    fd.close()
    sqlCommands = sqlFile.split(';')
    for command in sqlCommands:
        try:
            if command.strip() != '':
                cursor.execute(command)
        except mysql.connector.Error as e:
            log += "\n"+str(e)
            
    return log

def listdir_nohidden(path):
    cmd = "ls -1 "+path+" | sort | uniq -u | sort -V"
    raw = os.popen(cmd).read().split('\n')
    #remove empty element remained at the end
    clean = [x for x in raw if x]
    return clean

def updateDB(db, user, p, host, port):
    ret = ""
    cnx=mysql.connector.connect(
        database=db,
        host=host,
        user=user,
        passwd=p,
        port=port
    )
    cursor=cnx.cursor()
    #check if update_db table exists
    ret += "\nINFO: Checking if update_db table exists:"
    query=("select count(*) from information_schema.tables where table_schema='"+db+"' and table_name='update_db';")
    cursor.execute(query)
    exist_table_rows = cursor.fetchall()
    for row in exist_table_rows:
        exist_table = row[0]
    if exist_table == 1:
        ret += "\nINFO: update_db table found."
        #get update_db table rows
        query=("SELECT DISTINCT name FROM update_db;")
        cursor.execute(query)
        update_db_rows = cursor.fetchall()
        exist_db = []
        for row in update_db_rows:
            exist_db.append(str(row[0]))
        ret += "\nINFO: Checking applied patches: "+str(cursor.rowcount)
        exist_patch = listdir_nohidden(scriptDir+'/../db/patch')
        ret += "\nINFO: Checking exist patches: "+str(len(exist_patch))
        not_exist_db = list(set(exist_patch) - set(exist_db))
    elif exist_table == 0:
        ret += "INFO: update_db table not found."
        not_exist_db = listdir_nohidden(scriptDir+'/../db/patch')
    if len(not_exist_db) > 0:
        ret += "\nINFO: Checking DB patches that are not applied: "
        for sql in not_exist_db:
            ret += "\nINFO: Database patch "+sql+" will be executed."
            err = executeScriptsFromFile(scriptDir+'/../db/patch/'+sql, cursor)
            if err:
                ret += err
                ret += "\nINFO: Database patch "+sql+" failed."
            else: 
                ret += "\nINFO: Database patch "+sql+" successfully executed."
            cnx.commit()
    else:
        ret += "\nINFO: No new DB patches found."

    ret += "\nINFO: Database update completed."
    cursor.close()
    return ret
    

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--version", type=str, help="Please enter the version or branch name to pull", default="master")
    args = parser.parse_args()
    conf=getConf()
    pull_cmd = "cd "+scriptDir+"/.. && git pull https://github.com/UMMS-Biocore/dolphinnext.git " + args.version + " 2>&1"
    print ("INFO: Pulling"+"\nRUN : "+ pull_cmd)
    pull_cmd_log = os.popen(pull_cmd).read()
    print ("\n"+"LOG :\n" + pull_cmd_log)
    print ("\nINFO:"+ " Database update initiated:")
    runUpdateLog = updateDB(conf['DB'], conf['DBUSER'], conf['DBPASS'], conf['DBHOST'], conf['DBPORT'])
    print (runUpdateLog)
    
    
if __name__ == "__main__":
    main()




