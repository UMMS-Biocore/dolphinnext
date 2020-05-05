*********************
Adding Public SSH Key
*********************

.. contents:: Table of Contents

This guide shows how to connect to your host machine and insert your public key into **authorized_keys** file. 

.. important:: The authorized_keys file specifies the SSH keys that can be used for logging into the user account. Please read following guide carefully, since it is a highly important configuration file.

.. raw:: html

    <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; height: auto;">
        <iframe src="https://www.youtube.com/embed/8y8Vo7iJh78" frameborder="0" allowfullscreen style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></iframe>
    </div>
    </br>

Step 1: Connecting to host machine
==================================

A. If you're using Windows
--------------------------
    
In order to make an SSH connection to your host machine, you need to use program like PuTTY.

    A.1. Download and open PuTTY from `their website. <https://www.putty.org/>`_

    A.2. Use the following info to configure your connection and click **Open** to start the SSH session.
    
        - Host Name:Enter the Internet Protocol (IP) address of your host. (eg. example.com OR s00000.testserver.com)
        - Port: 22 
        - Connection Type: SSH 
        
        .. image:: dolphinnext_images/putty.png
	       :align: center
	       :width: 60%

    A.3. If this is the first time that you have used PuTTY to log in to your account with SSH, a warning similar to the following one displays. If you are sure that you have entered the correct information, click Yes. 

        .. image:: dolphinnext_images/putty_warning.png
	       :align: center
	       :width: 50%

    A.4. After you accept the warning, the terminal prompts you for your username and password. Please enter these values and press enter.

        .. caution:: - The password is not echoed back to the screen as it is entered.
                    - If you need to **copy and paste your password**, you can right-click (or use middle mouse button) to paste your password.


    A.5. If this is the first time that you login to your host machine, you might need to reset your password. Please enter the new password and press Enter. Note that passwords are not echoed to the screen. After changing your password, you will be logged out and you need to reconnect to your host machine.

    A.6. If you have entered the correct password, the prompt responds with a shell prompt::

        [us2r@yourhostname ~]#

B. If you're using Linux or MacOS
---------------------------------

If you are a MacOS or Linux user, you can make an SSH connection by using **Terminal**, a console program included with the operating system.

    B.1. Search for the **Terminal** program in your operating system and click to open it.
    
        .. image:: dolphinnext_images/terminal.png
	       :align: center
	       :width: 80%
    
    B.2. First, you should type the ``ssh`` command in the console. Then enter your username and hostname and add ``@`` sign in between (eg. ``yourusername@yourhostname``, ``user@ghpcc06.umassrc.org``) and press enter::
    
        ssh us2r@yourhostname
        
    .. image:: dolphinnext_images/terminal_ssh.png
	   :align: center
	   :width: 95%
    
    B.3. The terminal prompts you for your password. Please enter your password and press enter.

        .. caution:: - The password is not echoed back to the screen as it is entered.
                    - You can still **copy and paste your password**, but it won't appear in your screen.
                    
    B.4. If this is the first time that you login to your host machine, you might need to reset your password. Please enter the new password and press Enter. Note that passwords are not echoed to the screen. After changing your password, you will be logged out and you need to reconnect to your host machine.

    B.5. If you have entered the correct password, the prompt responds with a shell prompt::

        [us2r@yourhostname ~]#

Step 2: Editing authorized_keys file
====================================

    1. In order to edit ``authorized_keys`` file, we will use vi editor. Please enter following command to open vi editor::
    
        vi ~/.ssh/authorized_keys
        
    2. Press ``i`` button to change the editor mode to **insert mode**. Now you're ready to insert new text into this file.
    3. Return back to DolphinNext website and copy **all of your public ssh key** (command + c for MacOS or ctrl + c for Linux/Windows).  
    
        .. caution:: Please don't forget to copy initial part of the ssh key(eg. ``ssh-rsa``). 
            It should cover all of the following example key file::
        
                        ssh-rsa
                        AA1AB3N4nX3a....................
                        ................................
                        ................................
                        ...............b9Rj @dolphinnext
            
    
    4. Return back to terminal/Putty and paste your key (command + v for MacOS, ctrl + v for Linux, **right-click** for Windows).  
    5. If you already have another public key in your file, please press **enter** to separate keys from each other.
    6. If you've successfully edited your file and ready to exit from the editor, please press ``ESC`` to exit from **insert mode** and type ``:wq!`` and press enter. 
    
        .. tip:: If something went wrong and you don't want to save you changes, then please press ``ESC`` to exit from **insert mode** and type ``:q!`` and press enter to exit.

Step 3: Setting correct directory permissions
=============================================

The SSH protocol requires following file/directory permissions to establish secure connections.
    
    1. Please execute following commands to make sure SSH related files are not writeable by other users::
    
        chmod 700 ~/.ssh
        chmod 600 ~/.ssh/authorized_keys
    
    .. caution:: Your home directory shoudn't be writeable by other users. If you need to share your files with everyone, don't set permission of your home directory to 777. It creates security issues and blocks ssh connection. You can set to more secure options such as 750, 755 or 754.
    







