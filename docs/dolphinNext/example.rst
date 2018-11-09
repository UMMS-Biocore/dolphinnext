*****************
Pipeline Examples
*****************

There are numerous publicly available pipelines and processes exist in `DolphinNext website <https://dolphinnext.umassmed.edu>`_. In the main page, you may click on **Learn More** button and investigate the pipelines you interested. In order to reach process details just click settings button at the top of the process circles. 

.. image:: dolphinnext_images/examples_public.png
	:align: center

piPipes
=======

piPipes is a set of pipelines developed in the Zamore Lab and ZLab to analyze piRNA/transposon from different Next Generation Sequencing libraries such as small RNA-seq, RNA-seq, Genome-seq, ChIP-seq, CAGE/Degradome-Seq. Please see their Wiki pages (https://github.com/bowhan/piPipes/wiki) for the original content.

These pipelines are loaded to DolphinNext to facilitate their usage. An singularity image is provided (shub://onuryukselen/piPipes_singularity) where all the neccessary programs are installed to execute the piPipes. Additionally, all of the pipelines requires parameterFile in which library specific parameters are defined. You can either run the default parameters by entering `default` as input for the parameterFilePath or you can create a custom parameter file and enter the full path of it. To download and edit parameter file you can use `this link <https://raw.githubusercontent.com/onuryukselen/piPipes_singularity/37e6778bbfae56844e7b722bd68876cb6a9cf862/files/parameterFile.txt>`_.

Content of the parameterFile.txt is showed at below::

    export rRNA_MM=1
    export hairpin_MM=1
    export genome_MM=1
    export transposon_MM=1
    export siRNA_bot=20
    export siRNA_top=22
    export piRNA_bot=23
    export piRNA_top=29


Explaination of the variables listed below::

    rRNA_MM: Number of mismatches should be allowed for rRNA mapping by bowtie
    hairpin_MM: Number of mismatches should be allowed for microRNA hairping mapping by bowtie
    genome_MM: Number of mismatches should be allowed for genome mapping by bowtie
    transposon_MM: Number of mismatches should be allowed for transposons/piRNAcluster mapping by bowtie
    siRNA_bot: shortest length for siRNA
    siRNA_top: longest length for siRNA
    piRNA_bot: shortest length for piRNA
    piRNA_top: longest length for piRNA




