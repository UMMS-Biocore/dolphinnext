**************************
Frequently Asked Questions
**************************

.. contents:: Table of Contents

Installation Guide
==================

Running on the Amazon or Google Cloud
-------------------------------------
We define ``localhost:8080`` in ``dolphinnext/config/.sec`` file and use that to log in or other operations. You need to change `localhost` to that IP address or amazon/google domain you use. So static IP address and domainname would solve the issue that you will not need to change it every time you create an instance. Please update ``BASE_PATH`` and ``PUBWEB_URL`` as follows::

    BASE_PATH = http://localhost:8080/dolphinnext
    PUBWEB_URL = http://localhost:8080/dolphinnext/tmp/pub

    to
    
    BASE_PATH = http://your_temporary_domain_name:8080/dolphinnext
    PUBWEB_URL = http://your_temporary_domain_name:8080/dolphinnext/tmp/pub

.. note:: Please don’t change other lines because others are used inside of docker.