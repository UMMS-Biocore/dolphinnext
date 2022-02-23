params.outdir = 'results'  

params.genome_build = "" //* @dropdown @options:"human_hg19, mouse_mm10, mousetest_mm10, zebrafish_danRer7, zebrafish_danRer10, zebrafish_GRCz11plus_ensembl, rat_rn6, rat_rn6ens, custom"
params.run_IGV_TDF_Conversion = "no" //* @dropdown @options:"yes","no" @show_settings:"IGV_BAM2TDF_converter"
params.run_RSeQC = "no" //* @dropdown @options:"yes","no"
params.run_Picard_CollectMultipleMetrics = "no" //* @dropdown @options:"yes","no"
params.run_BigWig_Conversion = "no" //* @dropdown @options:"yes","no"
params.run_ATAC_MACS2 = "yes" //* @dropdown @options:"yes","no" @show_settings:"ATAC_Prep","bedtools_coverage"


def _species;
def _build;
def _share;
//* autofill
_nucleicAcidType = "dna"
params.nucleicAcidType = "dna"
if (params.genome_build == "mousetest_mm10"){
    _species = "mousetest"
    _build = "mm10"
} else if (params.genome_build == "human_hg19"){
    _species = "human"
    _build = "hg19"
} else if (params.genome_build == "mouse_mm10"){
    _species = "mouse"
    _build = "mm10"
} else if (params.genome_build == "rat_rn6"){
    _species = "rat"
    _build = "rn6"
} else if (params.genome_build == "rat_rn6ens"){
    _species = "rat"
    _build = "rn6ens"
} else if (params.genome_build == "zebrafish_danRer7"){
    _species = "zebrafish"
    _build = "danRer7"
} else if (params.genome_build == "zebrafish_danRer10"){
    _species = "zebrafish"
    _build = "danRer10"
} else if (params.genome_build == "zebrafish_GRCz11plus_ensembl"){
    _species = "zebrafish"
    _build = "GRCz11plus_ensembl"
}
if ($HOSTNAME == "default"){
    _share = "/mnt/efs/share/genome_data"
    $SINGULARITY_IMAGE = "shub://UMMS-Biocore/singularitysc"
    $SINGULARITY_OPTIONS = "--bind /mnt"
}
//* platform
if ($HOSTNAME == "garberwiki.umassmed.edu"){
    _share = "/share/dolphin/genome_data"
    $SINGULARITY_IMAGE = "shub://UMMS-Biocore/singularitysc"
	$SINGULARITY_OPTIONS = "--bind /project --bind /share"
} else if ($HOSTNAME == "ghpcc06.umassrc.org"){
    _share = "/share/data/umw_biocore/genome_data"
    $SINGULARITY_IMAGE = "/project/umw_biocore/singularity/UMMS-Biocore-singularitysc-master-latest.simg"
	$SINGULARITY_OPTIONS = "--bind /project --bind /share --bind /nl"
    $TIME = 700
    $CPU  = 1
    $MEMORY = 32
    $QUEUE = "long"
}
//* platform
if (params.genome_build && $HOSTNAME){
    params.genomeDir ="${_share}/${_species}/${_build}/"
    params.genome ="${_share}/${_species}/${_build}/${_build}.fa"
    params.bed_file_genome ="${_share}/${_species}/${_build}/${_build}.bed"
    params.ref_flat ="${_share}/${_species}/${_build}/ref_flat"
    params.genomeSizePath ="${_share}/${_species}/${_build}/${_build}.chrom.sizes"
    params.gtfFilePath ="${_share}/${_species}/${_build}/ucsc.gtf"
    params.genomeIndexPath ="${_share}/${_species}/${_build}/${_build}"
    params.bowtieInd_rRNA = "${_share}/${_species}/${_build}/commondb/rRNA/rRNA"
    params.bowtieInd_ercc = "${_share}/${_species}/${_build}/commondb/ercc/ercc"
    params.bowtieInd_miRNA ="${_share}/${_species}/${_build}/commondb/miRNA/miRNA"
    params.bowtieInd_tRNA = "${_share}/${_species}/${_build}/commondb/tRNA/tRNA"
    params.bowtieInd_piRNA = "${_share}/${_species}/${_build}/commondb/piRNA/piRNA"
    params.bowtieInd_snRNA = "${_share}/${_species}/${_build}/commondb/snRNA/snRNA"
    params.bowtieInd_rmsk = "${_share}/${_species}/${_build}/commondb/rmsk/rmsk"
}
if ($HOSTNAME){
    params.igvtools_path = "/usr/local/bin/dolphin-bin/IGVTools/igvtools.jar"
    params.picard_path = "/usr/local/bin/dolphin-bin/picard-tools-1.131/picard.jar"
    params.bedtools_path = "/usr/local/bin/dolphin-bin/bedtools2/bedtools"
    params.samtools_path = "/usr/local/bin/dolphin-bin/samtools-1.2/samtools"
    params.bowtie2_path = "/usr/local/bin/dolphin-bin/bowtie2"
    params.trimmomatic_path = "/usr/local/bin/dolphin-bin/trimmomatic-0.32.jar"
    params.fastx_trimmer_path = "/usr/local/bin/dolphin-bin/fastx_trimmer"
    params.pdfbox_path = "/usr/local/bin/dolphin-bin/pdfbox-app-2.0.0-RC2.jar"
    params.genomeCoverageBed_path = "/usr/local/bin/dolphin-bin/genomeCoverageBed"
    params.wigToBigWig_path = "/usr/local/bin/dolphin-bin/wigToBigWig"
    
}
//*
if (!params.reads){params.reads = ""} 
if (!params.mate){params.mate = ""} 

Channel
	.fromFilePairs( params.reads , size: (params.mate != "pair") ? 1 : 2 )
	.ifEmpty { error "Cannot find any reads matching: ${params.reads}" }
	.into{g_1_reads_g47_0;g_1_reads_g47_3}

Channel.value(params.mate).into{g_2_mate_g_15;g_2_mate_g_16;g_2_mate_g_67;g_2_mate_g46_82;g_2_mate_g46_95;g_2_mate_g46_111;g_2_mate_g47_0;g_2_mate_g47_3;g_2_mate_g47_1;g_2_mate_g47_2;g_2_mate_g58_19;g_2_mate_g58_20;g_2_mate_g59_3;g_2_mate_g59_10}

params.run_FastQC = "no" //* @dropdown @options:"yes","no"
if (params.run_FastQC == "no") { println "INFO: FastQC will be skipped"}

process Adapter_Trimmer_Quality_Module_FastQC {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.(html|zip)$/) "fastqc/$filename"
}

input:
 val mate from g_2_mate_g47_3
 set val(name), file(reads) from g_1_reads_g47_3

output:
 file '*.{html,zip}'  into g47_3_FastQCout_g_70

errorStrategy 'retry'
maxRetries 3

when:
(params.run_FastQC && (params.run_FastQC == "yes"))

script:
nameAll = reads.toString()
if (nameAll.contains('.gz')) {
    file =  nameAll - '.gz' - '.gz'
    runGzip = "ls *.gz | xargs -i echo gzip -df {} | sh"
} else {
    file =  nameAll 
    runGzip = ''
}
"""
${runGzip}
fastqc ${file} 
"""
}

params.run_Adapter_Removal = "no" //* @dropdown @options:"yes","no" @show_settings:"Adapter_Removal"
params.trimmomatic_path = "" //* @input
Adapter_Sequence = "" //* @textbox @description:"Removes 3' Adapter Sequences. You can enter a single sequence or multiple sequences in different lines. Reverse sequences will not be removed." @tooltip:"Trimmomatic is used for adapter removal" 


if (!((params.run_Adapter_Removal && (params.run_Adapter_Removal == "yes")) || !params.run_Adapter_Removal)){
g_1_reads_g47_0.into{g47_0_reads_g47_1}
} else {

process Adapter_Trimmer_Quality_Module_Adapter_Removal {

input:
 set val(name), file(reads) from g_1_reads_g47_0
 val mate from g_2_mate_g47_0

output:
 set val(name), file("reads/*")  into g47_0_reads_g47_1

when:
(params.run_Adapter_Removal && (params.run_Adapter_Removal == "yes")) || !params.run_Adapter_Removal

shell:
nameAll = reads.toString()
nameArray = nameAll.split(' ')
file2 = ""
if (nameAll.contains('.gz')) {
    newName =  nameArray[0] - ~/(\.fastq.gz)?(\.fq.gz)?$/
    file1 =  nameArray[0] - '.gz' 
    if (mate == "pair") {file2 =  nameArray[1] - '.gz'}
    runGzip = "ls *.gz | xargs -i echo gzip -df {} | sh"
} else {
    newName =  nameArray[0] - ~/(\.fastq)?(\.fq)?$/
    file1 =  nameArray[0]
    if (mate == "pair") {file2 =  nameArray[1]}
    runGzip = ''
}
'''
#!/usr/bin/env perl
 use List::Util qw[min max];
 use strict;
 use File::Basename;
 use Getopt::Long;
 use Pod::Usage; 
 
system("mkdir reads adapter unpaired");

open(OUT, ">adapter/adapter.fa");
my @adaps=split(/\n/,"!{Adapter_Sequence}");
my $i=1;
foreach my $adap (@adaps)
{
 print OUT ">adapter$i\\n$adap\\n";
 $i++;
}
close(OUT);

my $cmd = "";

system("!{runGzip}");
$cmd="java -jar !{params.trimmomatic_path}";
if ("!{mate}" eq "pair") {
    system("$cmd PE -threads 1 -phred64 -trimlog !{name}.log !{file1} !{file2} reads/!{name}.1.fastq unpaired/!{name}.1.fastq.unpaired reads/!{name}.2.fastq unpaired/!{name}.1.fastq.unpaired ILLUMINACLIP:adapter/adapter.fa:1:30:5 MINLEN:20");
} else {
    system("$cmd SE -threads 1 -phred64 -trimlog !{name}.log !{file1} reads/!{name}.fastq ILLUMINACLIP:adapter/adapter.fa:1:30:5 MINLEN:15");
}

'''
}
}


params.run_Trimmer = "no" //* @dropdown @options:"yes","no" @show_settings:"Trimmer"
if (params.run_Trimmer != "yes") {println "INFO: Trimmer will be skipped"}
params.fastx_trimmer_path = "" //* @input 
single_or_paired_end_reads = "" //* @dropdown @options:"single","pair" 
trim_length_5prime = 0 //* @input @description:"Trimming length from 5' end" @tooltip:"Fastx trimmer (Version: 0.0.13) is used for trimming" 
trim_length_3prime = 0 //* @input @description:"Trimming length from 3' end" @tooltip:"Fastx trimmer (Version: 0.0.13) is used for trimming" 
trim_length_5prime_R1 = 0 //* @input @description:"Trimming length from 5' end of R1 reads" @tooltip:"Fastx trimmer (Version: 0.0.13) is used for trimming" 
trim_length_3prime_R1 = 0 //* @input @description:"Trimming length from 3' end of R1 reads" @tooltip:"Fastx trimmer (Version: 0.0.13) is used for trimming" 
trim_length_5prime_R2 = 0 //* @input @description:"Trimming length from 5' end of R2 reads" @tooltip:"Fastx trimmer (Version: 0.0.13) is used for trimming" 
trim_length_3prime_R2 = 0 //* @input @description:"Trimming length from 3' end of R2 reads" @tooltip:"Fastx trimmer (Version: 0.0.13) is used for trimming" 

//* @style @multicolumn:{trim_length_5prime,trim_length_3prime}, {trim_length_5prime_R1,trim_length_3prime_R1}, {trim_length_5prime_R2,trim_length_3prime_R2} @condition:{single_or_paired_end_reads="single", trim_length_5prime,trim_length_3prime}, {single_or_paired_end_reads="pair", trim_length_5prime_R1,trim_length_3prime_R1,trim_length_5prime_R2,trim_length_3prime_R2}

//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 240
    $CPU  = 1
    $MEMORY = 8
    $QUEUE = "short"
}
//* platform
//* autofill
if (!((params.run_Trimmer && (params.run_Trimmer == "yes")) || !params.run_Trimmer)){
g47_0_reads_g47_1.into{g47_1_reads_g47_2}
} else {

process Adapter_Trimmer_Quality_Module_Trimmer {

input:
 set val(name), file(reads) from g47_0_reads_g47_1
 val mate from g_2_mate_g47_1

output:
 set val(name), file("reads/*")  into g47_1_reads_g47_2

when:
(params.run_Trimmer && (params.run_Trimmer == "yes")) || !params.run_Trimmer

shell:
nameAll = reads.toString()
nameArray = nameAll.split(' ')
file2 = ""
if (nameAll.contains('.gz')) {
    newName =  nameArray[0] - ~/(\.fastq.gz)?(\.fq.gz)?$/
    file1 =  nameArray[0] - '.gz' 
    if (mate == "pair") {file2 =  nameArray[1] - '.gz'}
    runGzip = "ls *.gz | xargs -i echo gzip -df {} | sh"
} else {
    newName =  nameArray[0] - ~/(\.fastq)?(\.fq)?$/
    file1 =  nameArray[0]
    if (mate == "pair") {file2 =  nameArray[1]}
    runGzip = ''
}
'''
#!/usr/bin/env perl
 use List::Util qw[min max];
 use strict;
 use File::Basename;
 use Getopt::Long;
 use Pod::Usage; 
 
system("mkdir reads");

system("!{runGzip}");
if ("!{mate}" eq "pair") {
    my $file1 = "!{file1}";
    my $file2 = "!{file2}";
    my $trim1 = "!{trim_length_5prime_R1}:!{trim_length_3prime_R1}";
    my $trim2 = "!{trim_length_5prime_R2}:!{trim_length_3prime_R2}";
    my ($format, $len)=getFormat($file1);
    print "length of $file1: $len\\n";
    trimFiles($file1, $trim1, $format, $len);
    my ($format, $len)=getFormat($file2);
    print "length of $file2: $len\\n";
    trimFiles($file2, $trim2, $format, $len);
} else {
    my $file1 = "!{file1}";
    my $trim1 = "!{trim_length_5prime}:!{trim_length_3prime}";
    my ($format, $len)=getFormat($file1);
    print "length of file1 $len\\n";
    trimFiles($file1, $trim1, $format, $len);
}



sub trimFiles
{
  my ($file, $trim, $format, $len)=@_;
  my $cmd="!{params.fastx_trimmer_path}";
    my @nts=split(/[,:\\s\\t]+/,$trim);
    my $inpfile="";
    my $com="";
    my $i=1;
    my $outfile="";
    my $param="";
    my $quality="";
    #if ($format eq "sanger")
    #{   
      $quality="-Q33";
    #}
    if (scalar(@nts)==2)
    {
      $param = "-f ".($nts[0]+1) if (exists($nts[0]) && $nts[0] >= 0 );
      $param .= " -l ".($len-$nts[1]) if (exists($nts[0]) && $nts[1] > 0 );
      print "parameters for $file: $param \\n ";
      $outfile="reads/$file";  
      $com="$cmd $quality -v $param -o $outfile -i $file " if ((exists($nts[0]) && $nts[0] > 0) || (exists($nts[0]) && $nts[1] > 0 ));
      print "$com\\n";
      if ($com eq ""){
          print "trimmer skipped for $file \\n";
          system("mv $file reads/.");
      } else {
          system("$com");
          print "trimmer executed for $file \\n";
      }
    }

    
}

# automatic format detection
sub getFormat
{
   my ($filename)=@_;

   # set function variables
   open (IN, $filename);
   my $j=1;
   my $qualities="";
   my $len=0;
   while(my $line=<IN>)
   {
     chomp($line);
     if ($j >50) { last;}
     if ($j%4==0)
     {
        $qualities.=$line;
        $len=length($line);
     }
     $j++;
   }
   close(IN);
  
   my $format = "";

   # set regular expressions
   my $sanger_regexp = qr/[!"#$%&'()*+,-.\\/0123456789:]/;
   my $solexa_regexp = qr/[\\;<=>\\?]/;
   my $solill_regexp = qr/[JKLMNOPQRSTUVWXYZ\\[\\]\\^\\_\\`abcdefgh]/;
   my $all_regexp = qr/[\\@ABCDEFGHI]/;

   # set counters
   my $sanger_counter = 0;
   my $solexa_counter = 0;
   my $solill_counter = 0;

   # check qualities
   if( $qualities =~ m/$sanger_regexp/ ){
          $sanger_counter = 1;
   }
   if( $qualities =~ m/$solexa_regexp/ ){
          $solexa_counter = 1;
   }
   if( $qualities =~ m/$solill_regexp/ ){
          $solill_counter = 1;
   }

   # determine format
   if( $sanger_counter ){
        $format = "sanger";
    }elsif($solexa_counter ){
        $format = "solexa";
    }elsif($solill_counter ){
        $format = "illumina";
    }

    # return file format
    return( $format,$len );
}

'''
}
}


params.run_Quality_Filtering = "no" //* @dropdown @options:"yes","no" @show_settings:"Quality_Filtering"
if (params.run_Quality_Filtering != "yes") {println "INFO: Quality_Filtering will be skipped"}
params.trimmomatic_path = "" //* @input
window_size = 10 //* @input @description:"Performs a sliding window trimming approach. It starts scanning at the 5' end and clips the read once the average quality within the window falls below a threshold (=required_quality). " @tooltip:"Trimmomatic 0.32 is used for quality filtering" 
required_quality_for_window_trimming = 15 //* @input @description:"specifies the average quality required for window trimming approach" @tooltip:"Trimmomatic 0.32 is used for quality filtering" 
leading = 5 //* @input @description:"Cut bases off the start of a read, if below a threshold quality" @tooltip:"Trimmomatic 0.32 is used for quality filtering" 
trailing = 5 //* @input @description:"Cut bases off the end of a read, if below a threshold quality" @tooltip:"Trimmomatic 0.32 is used for quality filtering" 
minlen = 36 //* @input @description:"Specifies the minimum length of reads to be kept" @tooltip:"Trimmomatic 0.32 is used for quality filtering" 
//* @style @multicolumn:{window_size,required_quality}, {leading,trailing,minlen}

//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 240
    $CPU  = 1
    $MEMORY = 8
    $QUEUE = "short"
}
//* platform
//* autofill
if (!((params.run_Quality_Filtering && (params.run_Quality_Filtering == "yes")) || !params.run_Quality_Filtering)){
g47_1_reads_g47_2.into{g47_2_reads_g58_19}
} else {

process Adapter_Trimmer_Quality_Module_Quality_Filtering {

input:
 set val(name), file(reads) from g47_1_reads_g47_2
 val mate from g_2_mate_g47_2

output:
 set val(name), file("reads/*")  into g47_2_reads_g58_19

when:
(params.run_Quality_Filtering && (params.run_Quality_Filtering == "yes")) || !params.run_Quality_Filtering    

shell:
nameAll = reads.toString()
nameArray = nameAll.split(' ')
file2 ="";
if (nameAll.contains('.gz')) {
    newName =  nameArray[0] - ~/(\.fastq.gz)?(\.fq.gz)?$/
    file1 =  nameArray[0] - '.gz' 
    if (mate == "pair") {file2 =  nameArray[1] - '.gz'}
    runGzip = "ls *.gz | xargs -i echo gzip -df {} | sh"
} else {
    newName =  nameArray[0] - ~/(\.fastq)?(\.fq)?$/
    file1 =  nameArray[0]
    if (mate == "pair") {file2 =  nameArray[1]}
    runGzip = ''
}
'''
#!/usr/bin/env perl
 use List::Util qw[min max];
 use strict;
 use File::Basename;
 use Getopt::Long;
 use Pod::Usage; 
 
 system("mkdir reads unpaired publish");

 if ("!{params.run_Quality_Filtering}" eq "yes") {
    system("!{runGzip}");
    my $param = "SLIDINGWINDOW:"."!{window_size}".":"."!{required_quality_for_window_trimming}";
    $param.=" LEADING:"."!{leading}";
    $param.=" TRAILING:"."!{trailing}";
    $param.=" MINLEN:"."!{minlen}";
    my $format=getFormat("!{file1}");
    my $quality="";
    if ($format eq "sanger")
    {   
        $quality="-phred33";
    }
    elsif ($format eq "illumina")
    {
    $quality="-phred64";
    }
    else {
    $quality="-phred33";    
    }
    print($quality);
     
    my $cmd="java -jar !{params.trimmomatic_path}";
    if ("!{mate}" eq "pair") {
        system("$cmd PE -threads 1 $quality -trimlog !{name}.log !{file1} !{file2} reads/!{name}.1.fastq unpaired/!{name}.1.fastq.unpaired reads/!{name}.2.fastq unpaired/!{name}.1.fastq.unpaired $param");
    } else {
        system("$cmd SE -threads 1 $quality -trimlog !{name}.log !{file1} reads/!{name}.fastq $param");
    }
    system("cd publish && ln -s ./../reads/* .");
 } else {
    system("mv !{reads} reads/.");
    system("touch publish/Quality_Filtering.skipped");
 }

# automatic format detection
sub getFormat
{
   my ($filename)=@_;

   # set function variables
   open (IN, $filename);
   my $j=1;
   my $qualities="";
   while(my $line=<IN>)
   {
     if ($j >50) { last;}
     if ($j%4==0)
     {
        $qualities.=$line;
     }
     $j++;
   }
   close(IN);
  
   my $format = "";

   # set regular expressions
   my $sanger_regexp = qr/[!"#$%&'()*+,-.\\/0123456789:]/;
   my $solexa_regexp = qr/[\\;<=>\\?]/;
   my $solill_regexp = qr/[JKLMNOPQRSTUVWXYZ\\[\\]\\^\\_\\`abcdefgh]/;
   my $all_regexp = qr/[\\@ABCDEFGHI]/;

   # set counters
   my $sanger_counter = 0;
   my $solexa_counter = 0;
   my $solill_counter = 0;

   # check qualities
   if( $qualities =~ m/$sanger_regexp/ ){
          $sanger_counter = 1;
   }
   if( $qualities =~ m/$solexa_regexp/ ){
          $solexa_counter = 1;
   }
   if( $qualities =~ m/$solill_regexp/ ){
          $solill_counter = 1;
   }

   # determine format
   if( $sanger_counter ){
        $format = "sanger";
    }elsif($solexa_counter ){
        $format = "solexa";
    }elsif($solill_counter ){
        $format = "illumina";
    }

    # return file format
    return( $format );
}

'''
}
}


params.bowtie2_path = "" //* @input
params.samtools_path = "" //* @input
params.bowtieInd_rRNA = "" //* @input
params.bowtieInd_ercc = "" //* @input
params.bowtieInd_miRNA = "" //* @input
params.bowtieInd_tRNA = "" //* @input
params.bowtieInd_piRNA = "" //* @input
params.bowtieInd_snRNA = "" //* @input
params.bowtieInd_rmsk = "" //* @input
params.run_Sequential_Mapping = "no" //* @dropdown @options:"yes","no" @show_settings:"Sequential_Mapping"

bowtieIndexes = [rRNA: params.bowtieInd_rRNA, 
                 ercc: params.bowtieInd_ercc,
                 miRNA: params.bowtieInd_miRNA,
                 tRNA: params.bowtieInd_tRNA,
                 piRNA: params.bowtieInd_piRNA,
                 snRNA: params.bowtieInd_snRNA,
                 rmsk: params.bowtieInd_rmsk]

//_nucleicAcidType="dna" should be defined in the autofill section of pipeline header in case dna is used.
_select_sequence = "" //* @dropdown @description:"Select sequence for mapping" @title:"Sequence Set for Mapping" @options:{"rRNA","ercc","miRNA","tRNA","piRNA","snRNA","rmsk","custom"},{_nucleicAcidType="dna","ercc","rmsk","custom"}
index_directory  = "" //* @input  @description:"index directory of sequence(full path)" @tooltip:"In order to map your reads to a custom sequence, you first must create an index file and that fasta must be in the same folder. Please remove all spacing from the naming of sequences within your fasta file in order for our pipeline to properly prepare quantification tables. The index directory must include the full path and the name of the index file must only be the prefix of the fasta. Index files and Fasta files also need to have the same prefix." 
name_of_the_index_file = "" //* @input  @autofill:{_select_sequence=("rRNA","ercc","miRNA","tRNA","piRNA","snRNA","rmsk"), _select_sequence},{_select_sequence="custom", " "} @description:"Name of the index file (prefix)" @tooltip:"In order to map your reads to a custom sequence, you first must create an index file and that fasta must be in the same folder. Please remove all spacing from the naming of sequences within your fasta file in order for our pipeline to properly prepare quantification tables. The index directory must include the full path and the name of the index file must only be the prefix of the fasta. Index files and Fasta files also need to have the same prefix." 
bowtie_Parameters = "-N 1" //* @input @description:"Bowtie2 parameters." 
description = "" //* @input @autofill:{_select_sequence=("rRNA","ercc","miRNA","tRNA","piRNA","snRNA","rmsk"), _select_sequence},{_select_sequence="custom", " "} @description:"Description of index file (please don't use comma or quotes in this field" 
filter_Out = "Yes" //* @dropdown @dropdown @options:"Yes","No" @description:"Select whether or not you want the reads mapped to this index filtered out of your total reads." 

desc_all=[]
description.eachWithIndex() {param,i -> 
    if (param.isEmpty()){
        desc_all[i] = name_of_the_index_file[i]
    }  else {
        desc_all[i] = param.replaceAll("[ |.|;]", "_")
    }
} 
custom_index=[]
index_directory.eachWithIndex() {param,i -> 
    if (_select_sequence[i] != "custom"){
        custom_index[i] = bowtieIndexes[_select_sequence[i]]
    } else {
        custom_index[i] = param+"/"+name_of_the_index_file[i] 
    }
}

mapList = []
paramList = []
filterList = []
indexList = []

//concat default mapping and custom mapping
mapList = (desc_all) 
paramList = (bowtie_Parameters)
filterList = (filter_Out)
indexList = (custom_index)

mappingList = mapList.join(" ") // convert into space separated format in order to use in bash for loop
paramsList = paramList.join(",") // convert into comma separated format in order to use in as array in bash
filtersList = filterList.join(",") // convert into comma separated format in order to use in as array in bash
indexesList = indexList.join(",") // convert into comma separated format in order to use in as array in bash
//* @style @condition:{_select_sequence="custom", index_directory,name_of_the_index_file,description,bowtie_Parameters,filter_Out},{_select_sequence=("rRNA","ercc","miRNA","tRNA","piRNA","snRNA","rmsk"),bowtie_Parameters,filter_Out}  @array:{_select_sequence,_select_sequence, index_directory,name_of_the_index_file,bowtie_Parameters,filter_Out,description} @multicolumn:{_select_sequence,_select_sequence,index_directory,name_of_the_index_file,bowtie_Parameters,filter_Out, description}


//* autofill
if ($HOSTNAME == "default"){
    $CPU  = 1
    $MEMORY = 20
}
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 2500
    $CPU  = 1
    $MEMORY = 20
    $QUEUE = "long"
}
//* platform
//* autofill
if (!(params.run_Sequential_Mapping == "yes")){
g47_2_reads_g58_19.into{g58_19_reads_g_67}
g58_19_bowfiles_g58_20 = Channel.empty()
g58_19_bowfiles_g_70 = Channel.empty()
g58_19_bam_file_g58_23 = Channel.empty()
g58_19_bam_index_g58_23 = Channel.empty()
g58_19_filter_g58_20 = Channel.empty()
} else {

process Sequential_Mapping_Module_Sequential_Mapping {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /bowfiles\/.*$/) "sequential_mapping/$filename"
	else if (filename =~ /.*\/.*sorted.bam$/) "sequential_mapping/$filename"
	else if (filename =~ /.*\/.*sorted.bam.bai$/) "sequential_mapping/$filename"
}

input:
 set val(name), file(reads) from g47_2_reads_g58_19
 val mate from g_2_mate_g58_19

output:
 set val(name), file("final_reads/*")  into g58_19_reads_g_67
 set val(name), file("bowfiles/*") optional true  into g58_19_bowfiles_g58_20, g58_19_bowfiles_g_70
 file "*/*sorted.bam" optional true  into g58_19_bam_file_g58_23
 file "*/*sorted.bam.bai" optional true  into g58_19_bam_index_g58_23
 val filtersList  into g58_19_filter_g58_20

when:
params.run_Sequential_Mapping == "yes"

script:
nameAll = reads.toString()
nameArray = nameAll.split(' ')
def file2;

if (nameAll.contains('.gz')) {
    newName =  nameArray[0] - ~/(\.fastq.gz)?(\.fq.gz)?$/
    file1 =  nameArray[0] - '.gz' 
    if (mate == "pair") {file2 =  nameArray[1] - '.gz'}
    runGzip = "ls *.gz | xargs -i echo gzip -df {} | sh"
} else {
    newName =  nameArray[0] - ~/(\.fastq)?(\.fq)?$/
    file1 =  nameArray[0]
    if (mate == "pair") {file2 =  nameArray[1]}
    runGzip = ''
}

"""
#!/bin/bash
mkdir reads final_reads bowfiles 
if [ -n "${mappingList}" ]; then
    $runGzip
    #rename files to standart format
    if [ "${mate}" == "pair" ]; then
        mv $file1 ${name}.1.fastq 2>/dev/null
        mv $file2 ${name}.2.fastq 2>/dev/null
        mv ${name}.1.fastq ${name}.2.fastq reads/.
    else
        mv $file1 ${name}.fastq 2>/dev/null
        mv ${name}.fastq reads/.
    fi
    #sequential mapping
    k=0
    prev="reads"
    IFS=',' read -r -a paramsListAr <<< "${paramsList}" #create comma separated array 
    IFS=',' read -r -a filtersListAr <<< "${filtersList}"
    IFS=',' read -r -a indexesListAr <<< "${indexesList}"
    wrkDir=\$(pwd)
    for rna_set in ${mappingList}
    do
        ((k++))
        printf -v k2 "%02d" "\$k" #turn into two digit format
        mkdir -p \${rna_set}/unmapped
        cd \$rna_set
        if [ "\${filtersListAr[\$k-1]}" == "Yes" ]; then
            ln -s \${wrkDir}/\${prev}/* .
            prev=\${rna_set}/unmapped
        else
            ln -s \${wrkDir}/\${prev}/* .
        fi
        if [ -e "\${indexesListAr[\$k-1]}.1.bt2" -o  -e "\${indexesListAr[\$k-1]}.fa"  -o  -e "\${indexesListAr[\$k-1]}.fasta" ]; then
            if [ -e "\${indexesListAr[\$k-1]}.fa" ] ; then
                fasta=\${indexesListAr[\$k-1]}.fa
            elif [ -e "\${indexesListAr[\$k-1]}.fasta" ] ; then
                fasta=\${indexesListAr[\$k-1]}.fasta
            fi
            if [ -e "\${indexesListAr[\$k-1]}.1.bt2" ] ; then
                echo "\${indexesListAr[\$k-1]}.1.bt2 index found."
            elif [ -e "\${indexesListAr[\$k-1]}.fa" ] ; then
                ${params.bowtie2_path}-build \${indexesListAr[\$k-1]}.fa \${indexesListAr[\$k-1]}
            elif [ -e "\${indexesListAr[\$k-1]}.fasta" ] ; then
                ${params.bowtie2_path}-build \${indexesListAr[\$k-1]}.fasta \${indexesListAr[\$k-1]}
            fi
                
            if [ "${mate}" == "pair" ]; then
                ${params.bowtie2_path} \${paramsListAr[\$k-1]} -x \${indexesListAr[\$k-1]} --no-unal --un-conc unmapped/${name}.unmapped.fastq -1 ${name}.1.fastq -2 ${name}.2.fastq --al-conc ${name}.fq.mapped -S \${rna_set}_${name}_alignment.sam > \${k2}_${name}.bow_\${rna_set} 2>&1
            else
                ${params.bowtie2_path} \${paramsListAr[\$k-1]} -x \${indexesListAr[\$k-1]} --no-unal --un  unmapped/${name}.unmapped.fastq -U ${name}.fastq --al ${name}.fq.mapped -S \${rna_set}_${name}_alignment.sam > \${k2}_${name}.bow_\${rna_set} 2>&1
            fi
            ${params.samtools_path} view -bT \$fasta \${rna_set}_${name}_alignment.sam > \${rna_set}_${name}_alignment.bam
            if [ "${mate}" == "pair" ]; then
                mv \${rna_set}_${name}_alignment.bam \${rna_set}_${name}_alignment.tmp1.bam
                ${params.samtools_path} sort -n \${rna_set}_${name}_alignment.tmp1.bam \${rna_set}_${name}_alignment.tmp2
                ${params.samtools_path} view -bf 0x02 \${rna_set}_${name}_alignment.tmp2.bam >\${rna_set}_${name}_alignment.bam
            fi
            ${params.samtools_path} sort \${rna_set}_${name}_alignment.bam \${rna_set}@${name}_sorted
            ${params.samtools_path} index \${rna_set}@${name}_sorted.bam
        
            for file in unmapped/*; do mv \$file \${file/.unmapped/}; done ##remove .unmapped from filename
            grep -v Warning \${k2}_${name}.bow_\${rna_set} > ${name}.tmp
            mv ${name}.tmp \${k2}_${name}.bow_\${rna_set}
            cp \${k2}_${name}.bow_\${rna_set} ./../bowfiles/.
            cd ..
        else
            cd unmapped 
            ln -s \${wrkDir}/\${rna_set}/*fastq .
            cd ..
            cd ..
        fi
    done
    cd final_reads && ln -s \${wrkDir}/\${prev}/* .
else 
    mv ${reads} final_reads/.
fi
"""
}
}


mappingListQuoteSep = mapList.collect{ '"' + it + '"'}.join(",") 
rawIndexList = indexList.collect{ '"' + it + '"'}.join(",") 
process Sequential_Mapping_Module_Sequential_Mapping_Bam_count {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.counts.tsv$/) "sequential_mapping_counts/$filename"
}

input:
 file bam from g58_19_bam_file_g58_23.collect()
 file index from g58_19_bam_index_g58_23.collect()

output:
 file "*.counts.tsv"  into g58_23_outputFileTSV

shell:
'''
#!/usr/bin/env perl
use List::Util qw[min max];
use File::Basename;
use Getopt::Long;
use Pod::Usage;
use Data::Dumper;

my @header;
my %all_files;

my @mappingList = (!{mappingListQuoteSep});
my @rawIndexList = (!{rawIndexList});
my %indexHash;
my $dedup = "";
@indexHash{@mappingList} = @rawIndexList;

chomp(my $contents = `ls *.bam`);
my @files = split(/[\\n]+/, $contents);
foreach my $file (@files){
        $file=~/(.*)@(.*)_sorted(.*)\\.bam/;
        my $mapper = $1; 
        my $name = $2; ##header
        print $3;
        if ($3 eq ".dedup"){
            $dedup = "dedup.";
        }
        push(@header, $name) unless grep{$_ eq $name} @header; #mapped element header
        push @{$all_files{$mapper}}, $file;
}


open OUT, ">header.tsv";
print OUT join ("\\t", "id","len",@header),"\\n";
close OUT;

foreach my $key (sort keys %all_files) {  
    my @array = @{ $all_files{$key} };  
        unless (-e "$indexHash{$key}.bed") {
        print "2: bed not found run makeBed\\n";
            if (-e "$indexHash{$key}.fa") { 
                makeBed("$indexHash{$key}.fa", $key, "$indexHash{$key}.bed");
            } elsif(-e "$indexHash{$key}.fasta"){
                makeBed("$indexHash{$key}.fasta", $key, "$indexHash{$key}.bed");
            }
        }
    
        my $bamFiles = join ' ', @array;
        print "bedtools multicov -bams $bamFiles -bed $indexHash{$key}.bed >$key.${dedup}counts.tmp\\n";
        `bedtools multicov -bams $bamFiles -bed $indexHash{$key}.bed >$key.${dedup}counts.tmp`;
        my $iniResColumn = int(countColumn("$indexHash{$key}.bed")) + 1;
        `awk -F \\"\\\\t\\" \\'{a=\\"\\";for (i=$iniResColumn;i<=NF;i++){a=a\\"\\\\t\\"\\$i;} print \\$4\\"\\\\t\\"(\\$3-\\$2)\\"\\"a}\\' $key.${dedup}counts.tmp> $key.${dedup}counts.tsv`;
        `sort -k3,3nr $key.${dedup}counts.tsv>$key.${dedup}sorted.tsv`;
        `cat header.tsv $key.${dedup}sorted.tsv> $key.${dedup}counts.tsv`;
}

sub countColumn {
    my ( \$file) = @_;
    open(IN, \$file);
    my $line=<IN>;
    chomp($line);
    my @cols = split('\\t', $line);
    my $n = @cols;
    close OUT;
    return $n;
}

sub makeBed {
    my ( \$fasta, \$type, \$bed) = @_;
    print "makeBed $fasta\\n";
    print "makeBed $bed\\n";
    open OUT, ">$bed";
    open(IN, \$fasta);
    my $name="";
    my $seq="";
    my $i=0;
    while(my $line=<IN>){
        chomp($line);
        if($line=~/^>(.*)/){
            $i++ if (length($seq)>0);
            print OUT "$name\\t1\\t".length($seq)."\\t$name\\t0\\t+\\n" if (length($seq)>0); 
            $name="$1";
            $seq="";
        } elsif($line=~/[ACGTNacgtn]+/){
            $seq.=$line;
        }
    }
    print OUT "$name\\t1\\t".length($seq)."\\t$name\\t0\\t+\\n" if (length($seq)>0); 
    close OUT;
}

'''


}

//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 240
    $CPU  = 1
    $MEMORY = 8
    $QUEUE = "short"
}
//* platform
//* autofill
process Sequential_Mapping_Module_parseBow {

input:
 set val(name), file(bowfile) from g58_19_bowfiles_g58_20
 val mate from g_2_mate_g58_20
 val filtersList from g58_19_filter_g58_20

output:
 file '*.tsv'  into g58_20_outputFileTSV_g58_13
 val "sequential_mapping_sum"  into g58_20_name_g58_13

shell:
'''
#!/usr/bin/env perl
open(my \$fh, '>', "!{name}.tsv");
print $fh "Sample\\tGroup\\tTotal Reads\\tReads After Filtering\\tUniquely Mapped\\tMultimapped\\tMapped\\n";
my @bowArray = split(' ', "!{bowfile}");
my $group= "\\t";
my @filterArray = (!{filtersList});
foreach my $bowitem(@bowArray) {
    # get mapping id
    my @bowAr = $bowitem.split("_");
    $bowCount = $bowAr[0] + -1;
    # if bowfiles ends with underscore (eg. bow_rRNA), parse rRNA as a group.
    if ($bowitem =~ m/bow_([^\\.]+)$/)  
    {
        $group = "$1\\t";
    } 
    open(IN, $bowitem);
    my $i = 0;
    my ($RDS_T, $RDS_P, $RDS_C1, $RDS_C2, $ALGN_T, $a, $b, $aPer, $bPer)=(0, 0, 0, 0, 0, 0, 0, 0, 0);
    while(my $line=<IN>)
    {
        chomp($line);
        $line=~s/^ +//;
        my @arr=split(/ /, $line);
        $RDS_T=$arr[0] if ($i=~/^1$/);
        
        if ($i == 2){
            if ($filterArray[$bowCount] eq "Yes"){
                 $RDS_P=$arr[0];
            } else {
                $RDS_P=$RDS_T;
            }
        }
        
        
        # Reads After Filtering column depends on filtering type
        if ($i == 3)
        {
            $a=$arr[0];
            $aPer=$arr[1];
            $aPer=~ s/([()])//g;
            $RDS_C1=$arr[0];
            
        }
        if ($i == 4)
        {
            $b=$arr[0];
            $bPer=$arr[1];
            $bPer=~ s/([()])//g;
            $RDS_C2=$arr[0];
        }
        $ALGN_T=($a+$b) if (($i == 5 && "!{mate}" ne "pair" ) || ($i == 13 && "!{mate}" eq "pair" )) ;
        $i++;
    }
    close(IN);
    print $fh "!{name}\\t$group$RDS_T\\t$RDS_P\\t$RDS_C1\\t$RDS_C2\\t$ALGN_T\\n";
}
close($fh);



'''

}


process Sequential_Mapping_Module_Merge_TSV_Files {

input:
 file tsv from g58_20_outputFileTSV_g58_13.collect()
 val outputFileName from g58_20_name_g58_13.collect()

output:
 file "${name}.tsv"  into g58_13_outputFileTSV_g58_14

script:
name = outputFileName[0]
"""    
awk 'FNR==1 && NR!=1 {  getline; } 1 {print} ' *.tsv > ${name}.tsv
"""
}


process Sequential_Mapping_Module_Sequential_Mapping_Short_Summary {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /sequential_mapping_detailed_sum.tsv$/) "sequential_mapping_summary/$filename"
}

input:
 file mainSum from g58_13_outputFileTSV_g58_14

output:
 file "sequential_mapping_short_sum.tsv"  into g58_14_outputFileTSV_g_65
 file "sequential_mapping_detailed_sum.tsv"  into g58_14_outputFile

shell:
'''
#!/usr/bin/env perl
use List::Util qw[min max];
use File::Basename;
use Getopt::Long;
use Pod::Usage;
use Data::Dumper;

my @header;
my %all_rows;
my @seen_cols_short;
my @seen_cols_detailed;
my $ID_header;

chomp(my $contents = `ls *.tsv`);
my @files = split(/[\\n]+/, $contents);
foreach my $file (@files){
        open IN,"$file";
        my $line1 = <IN>;
        chomp($line1);
        ( $ID_header, my @h) = ( split("\\t", $line1) );
        my $totalHeader = $h[1];
        my $afterFilteringHeader = $h[2];
        my $uniqueHeader = $h[3];
        my $multiHeader = $h[4];
        my $mappedHeader = $h[5];
        push(@seen_cols_short, $totalHeader) unless grep{$_ eq $totalHeader} @seen_cols_short; #Total reads Header
        push(@seen_cols_detailed, $totalHeader) unless grep{$_ eq $totalHeader} @seen_cols_detailed; #Total reads Header

        my $n=0;
        while (my $line=<IN>) {
                
                chomp($line);
                my ( $ID, @fields ) = ( split("\\t", $line) ); 
                #SHORT
                push(@seen_cols_short, $fields[0]) unless grep{$_ eq $fields[0]} @seen_cols_short; #mapped element header
                $all_rows{$ID}{$fields[0]} = $fields[5];#Mapped Reads
                #Grep first line $fields[1] as total reads.
                if (!exists $all_rows{$ID}{$totalHeader}){    
                        $all_rows{$ID}{$totalHeader} = $fields[1];
                } 
                $all_rows{$ID}{$afterFilteringHeader} = $fields[2]; #only use last entry
                #DETAILED
                $uniqueHeadEach = "$fields[0] (${uniqueHeader})";
                $multiHeadEach = "$fields[0] (${multiHeader})";
                $mappedHeadEach = "$fields[0] (${mappedHeader})";
                push(@seen_cols_detailed, $mappedHeadEach) unless grep{$_ eq $mappedHeadEach} @seen_cols_detailed;
                push(@seen_cols_detailed, $uniqueHeadEach) unless grep{$_ eq $uniqueHeadEach} @seen_cols_detailed;
                push(@seen_cols_detailed, $multiHeadEach) unless grep{$_ eq $multiHeadEach} @seen_cols_detailed;
                $all_rows{$ID}{$mappedHeadEach} = $fields[5];
                $all_rows{$ID}{$uniqueHeadEach} = $fields[3];
                $all_rows{$ID}{$multiHeadEach} = $fields[4];
    }
    close IN;
    push(@seen_cols_short, $afterFilteringHeader) unless grep{$_ eq $afterFilteringHeader} @seen_cols_short; #After filtering Header
}


#print Dumper \\%all_rows;
#print Dumper \\%seen_cols_short;

printFiles("sequential_mapping_short_sum.tsv",@seen_cols_short,);
printFiles("sequential_mapping_detailed_sum.tsv",@seen_cols_detailed);


sub printFiles {
    my($summary, @cols_to_print) = @_;
    
    open OUT, ">$summary";
    print OUT join ("\\t", $ID_header,@cols_to_print),"\\n";
    foreach my $key ( keys %all_rows ) { 
        print OUT join ("\\t", $key, (map { $all_rows{$key}{$_} // '' } @cols_to_print)),"\\n";
        }
        close OUT;
}

'''


}

params.run_Split_Fastq = "no" //* @dropdown @options:"yes","no" @show_settings:"SplitFastq"
readsPerFile = 5000000 //* @input @description:"The number of reads per file"
//Since splitFastq operator requires flat file structure, first convert grouped structure to flat, execute splitFastq, and then return back to original grouped structure
//.map(flatPairsClosure).splitFastq(splitFastqParams).map(groupPairsClosure)

//Mapping grouped read structure to flat structure
flatPairsClosure = {row -> if(row[1] instanceof Collection) {
        if (row[1][1]){
            tuple(row[0], file(row[1][0]), file(row[1][1]))
        } else {
            tuple(row[0], file(row[1][0]))
        }
    } else {
        tuple(row[0], file(row[1]))
    }
}

//Mapping flat read structure to grouped read structure
groupPairsClosure = {row -> tuple(row[0], (row[2]) ? [file(row[1]), file(row[2])] : [file(row[1])])}

// if mate of split process different than rest of the pipeline, use "mate_split" as input parameter. Otherwise use default "mate" as input parameter
mateParamName = (params.mate_split) ? "mate_split" : "mate"
splitFastqParams = ""
if (params[mateParamName] != "pair"){
    splitFastqParams = [by: readsPerFile, file:true]
}else {
    splitFastqParams = [by: readsPerFile, pe:true, file:true]
}

//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 240
    $CPU  = 1
    $MEMORY = 8
    $QUEUE = "short"
}
//* platform
//* autofill
if (!(params.run_Split_Fastq == "yes")){
g58_19_reads_g_67.into{g_67_reads_g59_3}
} else {

process SplitFastq {

input:
 val mate from g_2_mate_g_67
 set val(name), file(reads) from g58_19_reads_g_67.map(flatPairsClosure).splitFastq(splitFastqParams).map(groupPairsClosure)

output:
 set val(name), file("split/*")  into g_67_reads_g59_3

when:
params.run_Split_Fastq == "yes"

script:
"""    
mkdir -p split
mv ${reads} split/.
"""
}
}


params.bowtie2_path = "" //* @input
params.genomeIndexPath = "" //* @input
Map_Bowtie2_parameters = "" //* @input @description:"Bowtie2 parameters" 
//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 2000
    $CPU  = 1
    $MEMORY = 32
    $QUEUE = "long"
}
//* platform
//* autofill
process Bowtie2_Module_Map_Bowtie2 {

input:
 set val(name), file(reads) from g_67_reads_g59_3
 val mate from g_2_mate_g59_3

output:
 set val(name), file("${newName}.mapped*.fastq")  into g59_3_mapped_fastq
 set val(name), file("${newName}.bow")  into g59_3_bowfiles_g59_10, g59_3_bowfiles_g_70
 set val(name), file("${newName}.unmap*.fastq")  into g59_3_unmapped_fastq
 set val(name), file("${newName}_alignment.bam")  into g59_3_bam_file_g59_4

when:
(params.run_Bowtie2 && (params.run_Bowtie2 == "yes")) || !params.run_Bowtie2

script:
nameAll = reads.toString()
nameArray = nameAll.split(' ')
file2 = "";

if (nameAll.contains('.gz')) {
    newName =  nameArray[0] - ~/(\.fastq.gz)?(\.fq.gz)?$/
    file1 =  nameArray[0] - '.gz' 
    if (mate == "pair") {file2 =  nameArray[1] - '.gz'}
    runGzip = "ls *.gz | xargs -i echo gzip -df {} | sh"
} else {
    newName =  nameArray[0] - ~/(\.fastq)?(\.fq)?$/
    file1 =  nameArray[0]
    if (mate == "pair") {file2 =  nameArray[1]}
    runGzip = ''
}

""" 
    if [ "${mate}" == "pair" ]; then
        ${params.bowtie2_path} -x ${params.genomeIndexPath} ${Map_Bowtie2_parameters} --no-unal --un-conc ${newName}.unmapped.fastq -1 ${file1} -2 ${file2} --al-conc ${newName}.mapped.fastq -S ${newName}_alignment.sam > ${newName}.bow 2>&1
    else
        ${params.bowtie2_path} -x ${params.genomeIndexPath} ${Map_Bowtie2_parameters} --un ${newName}.unmapped.fastq -U ${file1} --al ${newName}.mapped.fastq -S ${newName}_alignment.sam > ${newName}.bow 2>&1
    fi
    grep -v Warning ${newName}.bow > ${newName}.tmp
    mv  ${newName}.tmp ${newName}.bow 
    samtools view -bS ${newName}_alignment.sam > ${newName}_alignment.bam 
"""

}

//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 240
    $CPU  = 1
    $MEMORY = 8
    $QUEUE = "short"
}
//* platform
//* autofill
process Bowtie2_Module_Bowtie_Summary {

input:
 set val(name), file(bowfile) from g59_3_bowfiles_g59_10.groupTuple()
 val mate from g_2_mate_g59_10

output:
 file '*.tsv'  into g59_10_outputFileTSV_g59_11
 val "bowtie_sum"  into g59_10_name_g59_11

shell:
'''
#!/usr/bin/env perl
open(my \$fh, '>', "!{name}.tsv");
print $fh "Sample\\tTotal Reads\\tUnique Reads Aligned (Bowtie2)\\tMultimapped Reads Aligned (Bowtie2)\\n";
my @bowArray = split(' ', "!{bowfile}");
my ($RDS_T, $RDS_C1, $RDS_C2)=(0, 0, 0);
foreach my $bowitem(@bowArray) {
    # get mapping id
    open(IN, $bowitem);
    my $i = 0;
    while(my $line=<IN>)
    {
        chomp($line);
        $line=~s/^ +//;
        my @arr=split(/ /, $line);
        $RDS_T+=$arr[0] if ($i=~/^1$/);
        if ($i == 3){
            $RDS_C1+=$arr[0];
        }
        if ($i == 4){
            $RDS_C2+=$arr[0];
        }
        $i++;
    }
    close(IN);
}
print $fh "!{name}\\t$RDS_T\\t$RDS_C1\\t$RDS_C2\\n";
close($fh);



'''

}


process Bowtie2_Module_Merge_TSV_Files {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /${name}.tsv$/) "bowtie2_summary/$filename"
}

input:
 file tsv from g59_10_outputFileTSV_g59_11.collect()
 val outputFileName from g59_10_name_g59_11.collect()

output:
 file "${name}.tsv"  into g59_11_outputFileTSV_g_65

script:
name = outputFileName[0]
"""    
awk 'FNR==1 && NR!=1 {  getline; } 1 {print} ' *.tsv > ${name}.tsv
"""
}

g59_11_outputFileTSV_g_65= g59_11_outputFileTSV_g_65.ifEmpty(file('starSum', type: 'any')) 
g58_14_outputFileTSV_g_65= g58_14_outputFileTSV_g_65.ifEmpty(file('sequentialSum', type: 'any')) 

//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 30
    $CPU  = 1
    $MEMORY = 10
    $QUEUE = "short"
}
//* platform
//* autofill
process Alignment_Summary {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /summary_data.tsv$/) "alignment_summary/$filename"
}

input:
 file starSum from g59_11_outputFileTSV_g_65
 file sequentialSum from g58_14_outputFileTSV_g_65

output:
 file "summary_data.tsv"  into g_65_outputFileTSV

shell:
'''
#!/usr/bin/env perl
use List::Util qw[min max];
use strict;
use File::Basename;
use Getopt::Long;
use Pod::Usage;
use Data::Dumper;

my @header;
my %all_rows;
my @seen_cols;
my $ID_header;

chomp(my $contents = `ls *.tsv`);
my @files = split(/[\\n]+/, $contents);
# if sequential_mapping_short_sum.tsv is exist push to the beginning of the array
my $checkSeqMap = "false";
my $checkSeqMapVal = "";
for my $index (reverse 0..$#files) {
    if ( $files[$index] =~ /sequential_mapping/ ) {
        $checkSeqMap = "true";
        $checkSeqMapVal = $files[$index];
        splice(@files, $index, 1, ());
    }
}
if ($checkSeqMap == "true"){
    unshift @files, $checkSeqMapVal;
}
##Merge each file according to array order

foreach my $file (@files){
        open IN,"$file";
        my $line1 = <IN>;
        chomp($line1);
        ( $ID_header, my @header) = ( split("\\t", $line1) );
        push @seen_cols, @header;

        while (my $line=<IN>) {
        chomp($line);
        my ( $ID, @fields ) = ( split("\\t", $line) ); 
        my %this_row;
        @this_row{@header} = @fields;

        #print Dumper \\%this_row;

        foreach my $column (@header) {
            if (! exists $all_rows{$ID}{$column}) {
                $all_rows{$ID}{$column} = $this_row{$column}; 
            }
        }   
    }
    close IN;
}

#print for debugging
#print Dumper \\%all_rows;
#print Dumper \\%seen_cols;

#grab list of column headings we've seen, and order them. 
my @cols_to_print = uniq(@seen_cols);
my $summary = "summary_data.tsv";
open OUT, ">$summary";
print OUT join ("\\t", $ID_header,@cols_to_print),"\\n";
foreach my $key ( keys %all_rows ) { 
    #map iterates all the columns, and gives the value or an empty string. if it's undefined. (prevents errors)
    print OUT join ("\\t", $key, (map { $all_rows{$key}{$_} // '' } @cols_to_print)),"\\n";
}
close OUT;

sub uniq {
    my %seen;
    grep ! $seen{$_}++, @_;
}

'''


}

params.samtools_path = "" //* @input

//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 240
    $CPU  = 1
    $MEMORY = 8
    $QUEUE = "short"
}
//* platform
//* autofill
process Bowtie2_Module_Merge_Bam {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*_sorted.*bam$/) "bowtie2/$filename"
}

input:
 set val(oldname), file(bamfiles) from g59_3_bam_file_g59_4.groupTuple()

output:
 set val(oldname), file("${oldname}.bam")  into g59_4_merged_bams
 set val(oldname), file("*_sorted*bai")  into g59_4_bam_index
 set val(oldname), file("*_sorted*bam")  into g59_4_sorted_bam_g_9, g59_4_sorted_bam_g46_109, g59_4_sorted_bam_g46_110, g59_4_sorted_bam_g46_111, g59_4_sorted_bam_g46_112

shell:
'''
num=$(echo "!{bamfiles.join(" ")}" | awk -F" " '{print NF-1}')
if [ "${num}" -gt 0 ]; then
    !{params.samtools_path} merge !{oldname}.bam !{bamfiles.join(" ")} && !{params.samtools_path} sort -O bam -T !{oldname} -o !{oldname}_sorted.bam !{oldname}.bam && !{params.samtools_path} index !{oldname}_sorted.bam
else
    mv !{bamfiles.join(" ")} !{oldname}.bam 2>/dev/null || true
    !{params.samtools_path} sort  -T !{oldname} -O bam -o !{oldname}_sorted.bam !{oldname}.bam && !{params.samtools_path} index !{oldname}_sorted.bam
fi
'''
}

params.bed_file_genome = "" //* @input
process BAM_Analysis_Module_RSeQC {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /result\/.*.out$/) "rseqc/$filename"
}

input:
 set val(name), file(bam) from g59_4_sorted_bam_g46_110

output:
 file "result/*.out"  into g46_110_outputFileOut_g46_95, g46_110_outputFileOut_g_70

when:
(params.run_RSeQC && (params.run_RSeQC == "yes")) || !params.run_RSeQC

script:
"""
mkdir result
read_distribution.py  -i ${bam} -r ${params.bed_file_genome}> result/RSeQC.${name}.out
"""
}


process BAM_Analysis_Module_RSeQC_Summary {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.tsv$/) "rseqc_summary/$filename"
}

input:
 file rseqcOut from g46_110_outputFileOut_g46_95.collect()
 val mate from g_2_mate_g46_95

output:
 file "*.tsv"  into g46_95_outputFileTSV

shell:
'''
#!/usr/bin/env perl
use List::Util qw[min max];
use strict;
use File::Basename;
use Getopt::Long;
use Pod::Usage; 
use Data::Dumper;

my $indir = $ENV{'PWD'};
my $outd = $ENV{'PWD'};
my @files = ();
my @outtypes = ("RSeQC");
my @order=( "Total Reads", "Total Tags" , "Total Assigned Tags", "CDS_Exons", "5'UTR_Exons", "3'UTR_Exons", "Introns", "TSS_up_1kb", "TSS_up_5kb", "TSS_up_10kb", "TES_down_1kb", "TES_down_5kb", "TES_down_10kb");
my %lines=(
  "Total Reads" => 1,
  "Total Tags" => 1,
  "Total Assigned Tags" => 1,
  "CDS_Exons" => 2,
  "5'UTR_Exons" => 2,
  "3'UTR_Exons" => 2,
  "Introns" => 2,
  "TSS_up_1kb" => 2,
  "TSS_up_5kb" => 2,
  "TSS_up_10kb" => 2,
  "TES_down_1kb" => 2,
  "TES_down_5kb" => 2,
  "TES_down_10kb" => 2
);


foreach my $outtype (@outtypes)
{

my $ext=".out";
@files = <$indir/$outtype*$ext>;

my @rowheaders=();
my @libs=();
my %vals=();
my %normvals=();
my $type = "rsem";

foreach my $d (@files){
  my $libname=basename($d, $ext);
  $libname=~s/RSeQC.//g;
  $libname=~s/rsem.out.//g;
  $libname=~s/.genome//g;
  print $libname."\\n";
  push(@libs, $libname); 
  getVals($d, $libname, \\%vals, \\%normvals, \\%lines);
}
#print Dumper(%vals);
#print Dumper(%normvals);

my $sizemetrics = keys %vals;
write_results("$outd/$outtype.$type.counts.tsv", \\@libs,\\%vals, \\@order, "region") if ($sizemetrics>0);
write_results("$outd/$outtype.$type.tagskb.tsv", \\@libs,\\%normvals, \\@order, "region") if ($sizemetrics>0);

}

sub write_results
{
  my ($outfile, $libs, $vals, $order, $name )=@_;
  open(OUT, ">$outfile");
  print OUT "$name\\t".join("\\t", @{$libs})."\\n";

  my $lib=${$libs}[0];
  foreach my $key ( @order )
  {
    if (exists ${$vals}{$lib}{$key}) {
    print OUT $key;
    foreach my $lib (@{$libs})
    {
      print OUT "\\t".${$vals}{$lib}{$key};
    } 
    print OUT "\\n";
    }
  }
  close(OUT);
}

sub getVals{
  my ($filename, $libname, $vals, $normvals, $lines)=@_;
  if (-e $filename){
     open(IN, $filename);
     while(my $line=<IN>)
     {
       chomp($line);
       my @vals_arr=split(/\\s{2,}/,$line);
       if (exists ${$lines}{$vals_arr[0]}) {
         my $idx=${$lines}{$vals_arr[0]};
         ${$vals}{$libname}{$vals_arr[0]}=$vals_arr[$idx] if (exists $vals_arr[$idx]);
         if ($idx==2) {
             ${$normvals}{$libname}{$vals_arr[0]}=$vals_arr[3] if (exists $vals_arr[3]);
         }
       }
     } 
  }
  
}
'''

}

params.pdfbox_path = "" //* @input
params.picard_path = "" //* @input
//* autofill
if ($HOSTNAME == "default"){
    $CPU  = 1
    $MEMORY = 32
}
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 240
    $CPU  = 1
    $MEMORY = 32
    $QUEUE = "short"
}
//* platform
//* autofill
process BAM_Analysis_Module_Picard {

input:
 set val(name), file(bam) from g59_4_sorted_bam_g46_109

output:
 file "*_metrics"  into g46_109_outputFileOut_g46_82
 file "results/*.pdf"  into g46_109_outputFilePdf_g46_82

when:
(params.run_Picard_CollectMultipleMetrics && (params.run_Picard_CollectMultipleMetrics == "yes")) || !params.run_Picard_CollectMultipleMetrics

script:
"""
java -jar ${params.picard_path} CollectMultipleMetrics OUTPUT=${name}_multiple.out VALIDATION_STRINGENCY=LENIENT INPUT=${bam}
mkdir results && java -jar ${params.pdfbox_path} PDFMerger *.pdf results/${name}_multi_metrics.pdf
"""
}


process BAM_Analysis_Module_Picard_Summary {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.tsv$/) "picard_summary/$filename"
	else if (filename =~ /results\/.*.pdf$/) "picard/$filename"
}

input:
 file picardOut from g46_109_outputFileOut_g46_82.collect()
 val mate from g_2_mate_g46_82
 file picardPdf from g46_109_outputFilePdf_g46_82.collect()

output:
 file "*.tsv"  into g46_82_outputFileTSV
 file "results/*.pdf"  into g46_82_outputFilePdf

shell:
'''
#!/usr/bin/env perl
use List::Util qw[min max];
use strict;
use File::Basename;
use Getopt::Long;
use Pod::Usage; 
use Data::Dumper;

system("mkdir results && mv *.pdf results/. ");

my $indir = $ENV{'PWD'};
my $outd = $ENV{'PWD'};
my @files = ();
my @outtypes = ("CollectRnaSeqMetrics", "alignment_summary_metrics", "base_distribution_by_cycle_metrics", "insert_size_metrics", "quality_by_cycle_metrics", "quality_distribution_metrics" );

foreach my $outtype (@outtypes)
{
my $ext="_multiple.out";
$ext.=".$outtype" if ($outtype ne "CollectRnaSeqMetrics");
@files = <$indir/*$ext>;

my @rowheaders=();
my @libs=();
my %metricvals=();
my %histvals=();

my $pdffile="";
my $libname="";
foreach my $d (@files){
  my $libname=basename($d, $ext);
  print $libname."\\n";
  push(@libs, $libname); 
  getMetricVals($d, $libname, \\%metricvals, \\%histvals, \\@rowheaders);
}

my $sizemetrics = keys %metricvals;
write_results("$outd/$outtype.stats.tsv", \\@libs,\\%metricvals, \\@rowheaders, "metric") if ($sizemetrics>0);
my $sizehist = keys %histvals;
write_results("$outd/$outtype.hist.tsv", \\@libs,\\%histvals, "none", "nt") if ($sizehist>0);

}

sub write_results
{
  my ($outfile, $libs, $vals, $rowheaders, $name )=@_;
  open(OUT, ">$outfile");
  print OUT "$name\\t".join("\\t", @{$libs})."\\n";
  my $size=0;
  $size=scalar(@{${$vals}{${$libs}[0]}}) if(exists ${$libs}[0] and exists ${$vals}{${$libs}[0]} );
  
  for (my $i=0; $i<$size;$i++)
  { 
    my $rowname=$i;
    $rowname = ${$rowheaders}[$i] if ($name=~/metric/);
    print OUT $rowname;
    foreach my $lib (@{$libs})
    {
      print OUT "\\t".${${$vals}{$lib}}[$i];
    } 
    print OUT "\\n";
  }
  close(OUT);
}

sub getMetricVals{
  my ($filename, $libname, $metricvals, $histvals,$rowheaders)=@_;
  if (-e $filename){
     my $nextisheader=0;
     my $nextisvals=0;
     my $nexthist=0;
     open(IN, $filename);
     while(my $line=<IN>)
     {
       chomp($line);
       @{$rowheaders}=split(/\\t/, $line) if ($nextisheader && !scalar(@{$rowheaders})); 
       if ($nextisvals) {
         @{${$metricvals}{$libname}}=split(/\\t/, $line);
         $nextisvals=0;
       }
       if($nexthist){
          my @vals=split(/[\\s\\t]+/,$line); 
          push(@{${$histvals}{$libname}}, $vals[1]) if (exists $vals[1]);
       }
       $nextisvals=1 if ($nextisheader); $nextisheader=0;
       $nextisheader=1 if ($line=~/METRICS CLASS/);
       $nexthist=1 if ($line=~/normalized_position/);
     } 
  }
  
}
'''

}

params.picard_path = "" //* @input
params.run_Picard_MarkDuplicates = "yes" //* @dropdown @options:"yes","no"

//* autofill
if ($HOSTNAME == "default"){
    $CPU  = 1
    $MEMORY = 32
}
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 240
    $CPU  = 1
    $MEMORY = 32
    $QUEUE = "short"
}
//* platform
//* autofill
if (!((params.run_Picard_MarkDuplicates && (params.run_Picard_MarkDuplicates == "yes")) || !params.run_Picard_MarkDuplicates)){
g59_4_sorted_bam_g_9.into{g_9_mapped_reads_g_15}
g_9_publish = Channel.empty()
} else {

process Picard_MarkDuplicates {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /bam\/${name}.bam$/) "picard_deduplication/$filename"
	else if (filename =~ /${name}.*$/) "picard_deduplication/$filename"
}

input:
 set val(name), file(bam) from g59_4_sorted_bam_g_9

output:
 set val(name), file("bam/${name}.bam")  into g_9_mapped_reads_g_15
 set val(name), file("${name}*")  into g_9_publish

when:
(params.run_Picard_MarkDuplicates && (params.run_Picard_MarkDuplicates == "yes")) || !params.run_Picard_MarkDuplicates     

script:
"""
mkdir bam
java -jar ${params.picard_path} MarkDuplicates OUTPUT=${name}_dedup.bam METRICS_FILE=${name}_PCR_duplicates  VALIDATION_STRINGENCY=LENIENT REMOVE_DUPLICATES=true INPUT=${bam} 
mv ${name}_dedup.bam bam/${name}.bam
grep "Unknown" *_PCR_duplicates|awk '{print "${name}\t" \$9}' > ${name}_picardDedup_summary.txt
"""
}
}


params.bedtools_path = "" //* @input
params.genomeSizePath = "" //* @input
macs2_callpeak_parameters = "--nomodel -q 0.0001 --call-summits" //* @input @description:"macs2 callpeak parameters"
band_width = "29" //* @input @description:"Band width for picking regions to compute fragment size."
bedtoolsCoverage_Parameters = "-sorted -nobuf -hist " //* @input @description:"bedtools Coverage parameters"
compare_Custom_Bed = ""  //* @input @description:"Enter custom bed file <full path> for comparison"
output_prefix = "" //* @input @title:"Input Definitions" @description:"Output files will be created by using output_prefix"
sample_prefix = "" //* @description:"Use prefix of the sample to match files. You can use comma separated format to enter multiples files. Eg.Sample1_L001,Sample2_L001" @input 
input_prefix = "" //* @description:"Use prefix of the input to match files. You can use comma separated format to enter multiples files. Eg.Sample1_L001,Sample2_L001" @input
//* @array:{output_prefix,sample_prefix,input_prefix} @multicolumn:{output_prefix,sample_prefix,input_prefix},{macs2_callpeak_parameters,band_width,bedtoolsCoverage_Parameters}
samplehash = [:]
inputhash = [:]
output_prefix.eachWithIndex { key, i -> inputhash[key] = input_prefix[i] }
output_prefix.eachWithIndex { key, i -> samplehash[key] = sample_prefix[i] }

// String nameList = output_prefix.collect { "\"$it\"" }.join( ' ' )
// String samplesList = sample_prefix.collect { "\"$it\"" }.join( ' ' )
// String inputsList = input_prefix.collect { "\"$it\"" }.join( ' ' )
process ATAC_Prep {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /bed\/${name}.bed$/) "atac/$filename"
}

input:
 val mate from g_2_mate_g_15
 set val(name), file(bam) from g_9_mapped_reads_g_15

output:
 file "bed/${name}.bed"  into g_15_bed_g_16
 file "bam/${name}.bam"  into g_15_bam_file_g_16
 val output_prefix  into g_15_name_g_16

when:
(params.run_ATAC_MACS2 && (params.run_ATAC_MACS2 == "yes")) || !params.run_ATAC_MACS2

script:
"""
mkdir -p bed bam
${params.bedtools_path} bamtobed -i ${bam} > ${name}.bed
mv ${bam} bam/${name}.bam
${params.bedtools_path} slop -s -i ${name}.bed -l 9 -r 0 -g ${params.genomeSizePath} | grep "-" | awk -v OFS="\t" '{print \$1,\$3-29,\$3,\$4,\$5,\$6}' >> ${name}.adjust.bed
${params.bedtools_path} slop -s -i ${name}.bed -l 9 -r 0 -g ${params.genomeSizePath} | grep "+" | awk -v OFS="\t" '{print \$1,\$2,\$2+29,\$4,\$5,\$6}' >> ${name}.adjust.bed
mv ${name}.adjust.bed bed/${name}.bed
"""
}

params.genomeSizePath = "" //* @input
params.bedtools_path = "" //* @input
params.samtools_path = "" //* @input

process ATAC_MACS2 {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /${name}.*$/) "atac/$filename"
}

input:
 val mate from g_2_mate_g_16
 file bam from g_15_bam_file_g_16.collect()
 file bed from g_15_bed_g_16.collect()
 val name from g_15_name_g_16.unique().flatten()

output:
 file "*.bed"  into g_16_bed_g_54
 set val(name), file("bam/*.bam")  into g_16_bam_file_g_44
 val compare_bed  into g_16_compare_bed_g_44
 file "${name}*"  into g_16_resultsdir_g_70

script:
genomeSizeText = ""
if (_build == "mm10"){
    genomeSizeText = "-g mm"
} else if (_build == "hg19"){
    genomeSizeText = "-g hs"
}

compare_bed = "merged.bed"
compare_Custom_Bed = compare_Custom_Bed.trim();
if (compare_Custom_Bed != ""){
    compare_bed = compare_Custom_Bed
}
inputsList = inputhash[name] 
samplesList = samplehash[name]

"""
echo ${samplesList}
echo ${inputsList}
echo $name
mkdir -p bam

#samplesList
samplesList="\$(echo -e "${samplesList}" | tr -d '[:space:]')" 
IFS=',' read -ra eachSampleAr <<< "\${samplesList}"
numSamples=\${#eachSampleAr[@]}
eachSampleArBed=( "\${eachSampleAr[@]/%/.bed }" )
eachSampleArBam=( "\${eachSampleAr[@]/%/.bam }" )
sample_set=\${eachSampleArBed[@]}
bam_set=\${eachSampleArBam[@]}

#inputsList
input_set=""
inputsList="\$(echo -e "${inputsList}" | tr -d '[:space:]')" 
if [ "\${inputsList}" != "" ]; then
    IFS=',' read -ra eachInputAr <<< "\${inputsList}"
    eachInputArbed=( "\${eachInputAr[@]/%/.bed }" )
    input_set="-c \${eachInputArbed[@]}" 
fi
echo \${eachSampleArBed[@]}
echo \${eachSampleArBam[@]}

macs2 callpeak --bw ${band_width} -t \${sample_set} \${input_set} -n ${name} ${genomeSizeText} ${macs2_callpeak_parameters}

#bam files
if [ "\$numSamples" -gt "1" ]; then
    samtools merge bam/${name}.bam \$bam_set
else 
    rsync -a  \$bam_set bam/${name}.bam
fi



"""
}

//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 240
    $CPU  = 1
    $MEMORY = 10
    $QUEUE = "short"
}
//* platform
//* autofill
process MultiQC {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /multiqc_report.html$/) "multiQC/$filename"
}

input:
 file "fastqc/*" from g47_3_FastQCout_g_70.flatten().toList()
 file "sequential_mapping/*" from g58_19_bowfiles_g_70.flatten().toList()
 file "macs/*" from g_16_resultsdir_g_70.flatten().toList()
 file "rseqc_bowtie/*" from g46_110_outputFileOut_g_70.flatten().toList()
 file "bowtie/*" from g59_3_bowfiles_g_70.flatten().toList()

output:
 file "multiqc_report.html" optional true  into g_70_htmlout

"""
multiqc -e general_stats -d -dd 2 .
"""
}


process bed_merge {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /merged.bed$/) "atac/$filename"
}

input:
 file bed from g_16_bed_g_54.collect()

output:
 file "merged.bed"  into g_54_bed_g_44

"""
 cat ${bed} | bedtools sort -i stdin | bedtools slop -i stdin -b 100 -g ${params.genomeSizePath} | bedtools merge -i stdin | awk '{print \$0"\t"\$1"_"\$2"_"\$3}' > merged.bed

"""
}

bedtoolsCoverage_Parameters = "-sorted -hist " //* @input @description:"bedtools Coverage parameters"
bedtoolsIntersect_Parameters = "" //* @input @description:"bedtools Intersect optional parameters"

params.bedtools_path = "" //* @input
params.samtools_path = "" //* @input
process bedtools_coverage {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.mean.txt$/) "atac/$filename"
}

input:
 val compare_bed from g_16_compare_bed_g_44
 file bed from g_54_bed_g_44
 set val(name), file(bam) from g_16_bam_file_g_44

output:
 file "*.mean.txt"  into g_44_outputFileTxt_g_43

"""
echo ${compare_bed}
if [ -s "${compare_bed}" ]; then 
    echo " bed file exists and is not empty "
        ${params.samtools_path} view -H ${name}.bam | grep -P "@SQ\\tSN:" | sed 's/@SQ\\tSN://' | sed 's/\\tLN:/\\t/' > ${name}_chroms
        ${params.bedtools_path} intersect -abam ${name}.bam -b ${compare_bed} > temp_${name}.bam
        ${params.bedtools_path} sort -faidx ${name}_chroms -i ${compare_bed}  | ${params.bedtools_path} coverage ${bedtoolsCoverage_Parameters} -a stdin -b temp_${name}.bam  > temp_${name}.bed
        awk '{\$NF=\$(NF-3)*\$NF;print }' OFS="\\t" temp_${name}.bed | grep -v all > temp_${name}_hist.bed
        l=`awk '{print NF}' temp_${name}_hist.bed | head -1 | awk '{print \$1-4}'`
        k=`awk '{print NF}' temp_${name}_hist.bed | head -1`
        bedtools groupby -i temp_${name}_hist.bed -g 1-\$l -c \$k -o sum > ${name}.mean.txt
        #rm -rf temp_*

else
  echo " bed file does not exist, or is empty "
  touch ${name}_empty.mean.txt
fi
"""
}


process ATAC_CHIP_summary {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.tsv$/) "atac_summary/$filename"
}

input:
 file file from g_44_outputFileTxt_g_43.collect()

output:
 file "*.tsv"  into g_43_outputFile

shell:
'''
#!/usr/bin/env perl

my $indir = $ENV{'PWD'};

opendir D, $indir or die "Could not open $indir\n";
my @alndirs = sort { $a cmp $b } grep /.txt/, readdir(D);
closedir D;
    
my @a=();
my %b=();
my %c=();
my $i=0;
foreach my $d (@alndirs){ 
    my $file = "${indir}/$d";
    print $d."\n";
    my $libname=$d;
    $libname=~s/\\.mean\\.txt//;
    print $libname."\n";
    $i++;
    $a[$i]=$libname;
    open IN,"${indir}/$d";
    $_=<IN>;
    while(<IN>)
    {
        my @v=split; 
        $b{$v[3]}{$i}=$v[4];
    }
    close IN;
}
my $outfile="${indir}/"."mean_counts.tsv";
open OUT, ">$outfile";
print OUT "Feature";

for(my $j=1;$j<=$i;$j++) {
    print OUT "\t$a[$j]";
}
print OUT "\n";
    
foreach my $key (keys %b){
    print OUT "$key";
    for(my $j=1;$j<=$i;$j++){
        print OUT "\t$b{$key}{$j}";
    }
    print OUT "\n";
}
close OUT;
'''
}

params.genomeCoverageBed_path = "" //* @input
params.wigToBigWig_path = "" //* @input
params.genomeSizePath = "" //* @input
process BAM_Analysis_Module_UCSC_BAM2BigWig_converter {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.bw$/) "bigwig/$filename"
}

input:
 set val(name), file(bam) from g59_4_sorted_bam_g46_112

output:
 file "*.bw"  into g46_112_outputFileBw

when:
(params.run_BigWig_Conversion && (params.run_BigWig_Conversion == "yes")) || !params.run_BigWig_Conversion

script:
nameAll = bam.toString()
if (nameAll.contains('_sorted.bam')) {
    runSamtools = ''
} else {
    runSamtools = "samtools sort " +bam+ " "+ name +"_sorted && samtools index "+ name+"_sorted.bam "
}
"""
$runSamtools
${params.genomeCoverageBed_path} -split -bg -ibam ${name}_sorted.bam -g ${params.genomeSizePath} > ${name}.bg 
${params.wigToBigWig_path} -clip -itemsPerSlot=1 ${name}.bg ${params.genomeSizePath} ${name}.bw 
"""
}

igv_extention_factor = "0" //* @input @description:"The read or feature is extended by the specified distance in bp prior to counting. This option is useful for chip-seq and rna-seq applications. The value is generally set to the average fragment length of the library minus the average read length." @tooltip:"igvtools is used"
igv_window_size = "5" //* @input  @description:"The window size over which coverage is averaged." @tooltip:"igvtools is used"

params.genome = "" //* @input
params.igvtools_path = "" //* @input
process BAM_Analysis_Module_IGV_BAM2TDF_converter {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.tdf$/) "igv_tdf_converter/$filename"
}

input:
 val mate from g_2_mate_g46_111
 set val(name), file(bam) from g59_4_sorted_bam_g46_111

output:
 file "*.tdf"  into g46_111_outputFileOut

when:
(params.run_IGV_TDF_Conversion && (params.run_IGV_TDF_Conversion == "yes")) || !params.run_IGV_TDF_Conversion

script:
pairedText = (params.nucleicAcidType == "dna" && mate == "pair") ? " --pairs " : ""
nameAll = bam.toString()
if (nameAll.contains('_sorted.bam')) {
    runSamtools = ''
} else {
    runSamtools = "samtools sort " +bam+ " "+ name +"_sorted && samtools index "+ name+"_sorted.bam "
}
"""
$runSamtools
java -Xmx1500m  -jar ${params.igvtools_path} count -w ${igv_window_size} -e ${igv_extention_factor} ${pairedText} ${name}_sorted.bam ${name}.tdf ${params.genome}
"""
}


workflow.onComplete {
println "##Pipeline execution summary##"
println "---------------------------"
println "##Completed at: $workflow.complete"
println "##Duration: ${workflow.duration}"
println "##Success: ${workflow.success ? 'OK' : 'failed' }"
println "##Exit status: ${workflow.exitStatus}"
}
