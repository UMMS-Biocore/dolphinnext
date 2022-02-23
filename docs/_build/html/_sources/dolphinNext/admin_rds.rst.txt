*************************
Amazon RDS MySQL Database
*************************

In this article, we are going to show how to connect to the Amazon RDS MySQL instance with dolphinNext for MySQL.

1. Open Amazon RDS console: First of all, sign in to the AWS Management Console and find RDS in the Database section of All Services and click to open the Amazon RDS Console.

.. image:: dolphinnext_images/rds1.png
    :align: center
    :width: 99%

2. Create an Amazon MySQL instance: Go to Databases on the side menu and click Create database. In this step, you will need to pick a database creation method, configure engine options, and select a database version. Afterwards, choose the MySQL engine and select the version.

.. image:: dolphinnext_images/rds2.png
    :align: center
    :width: 99%

3. Select a template: Next, you will need to select a sample template for your MySQL instance on Amazon RDS.

.. image:: dolphinnext_images/rds3.png
    :align: center
    :width: 99%

4. Provide settings for your MySQL database on AWS: Pleade provide a unique name for your instance and master username and password which will be used in DolphinNext configuration. 

.. image:: dolphinnext_images/rds4.png
    :align: center
    :width: 99%

5.  Continue configuring your MySQL instance: Next, define instance size and storage options.

.. image:: dolphinnext_images/rds5.png
    :align: center
    :width: 99%

6.  Define connectivity options:  Please provide the necessary connectivity configurations for your MySQL instance.

.. image:: dolphinnext_images/rds6.png
    :align: center
    :width: 99%

7. Define advanced settings: Here you can enable database backup, enhanced monitoring, and maintenance.

.. image:: dolphinnext_images/rds7.png
    :align: center
    :width: 99%
    
8. Launch MySQL instance on AWS: After you have defined all the settings, you can finally click Launch DB Instance.

As soon as the instance process is finished (it might take some time), you will be able to check the status, together with some other configuration information in the corresponding window as shown on the screenshot.

To view the details of your MySQL instance, click the database instance name. Check the details and click Create database to finalize the process.

.. image:: dolphinnext_images/rds8.png
    :align: center
    :width: 99%
    
9. Connecting DolphinNext to an Amazon RDS MySQL database: Lets open main DolphinNext configuration file (located here: ``dolphinnext/config/.sec``) and enter following information to establish our connection::

    [Dolphinnext]
    DB=mysql
    DBUSER=Jordan
    DBPASS=rdspassword
    DBHOST=***.http://us-east-2.rds.amazonaws.com
    DBPORT=3306

* **DB:** Database name.
* **DBUSER:** Name for a database user.
* **DBPASS:** Password for a database user.
* **DBHOST:** RDS MySQL Server Hostname.
* **DBPORT:** RDS MySQL Server Port.


    
    
    
    