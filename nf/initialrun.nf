// initial run for preparing files in the inputs directory.

// convert quoted string into list "a","b,c" -> ["a","b,c"]
def quo2Lst (str){
    list =str.split(",(?=(?:[^\"]*\"[^\"]*\")*[^\"]*\$)");
    return list
}

params.attempt = ""
params.run_dir = ""
params.profile = ""
params.given_name = ""
params.input_name = ""
params.url = ""
params.urlzip = ""
params.checkpath = ""
//url data
Channel.from(params.given_name).into{given_name1; given_name2}
Channel.from(params.input_name).into{input_name1; input_name2}
Channel.from(params.url).into{url1; url2}
Channel.from(params.urlzip).into{urlzip1; urlzip2}
Channel.from(params.checkpath).into{checkpath1; checkpath2}
//collection data
Channel.from(params.collection).into{collection1; collection2; collection3}
Channel.from(params.file_dir).into{file_dir1; file_dir2}
Channel.from(params.files_used).into{files_used1; files_used2}
Channel.from(params.archive_dir).into{archive_dir1; archive_dir2}
Channel.from(params.s3_archive_dir).into{s3_archive_dir1; s3_archive_dir2}
Channel.from(params.file_name).into{file_name1; file_name2; file_name3}
Channel.from(params.file_type).into{file_type1; file_type2; file_type3}
Channel.from(params.collection_type).into{collection_type1; collection_type2; collection_type3}

process initialCheck {
    input:
        val collection from collection1
        val file_name from file_name1
        val file_dir from file_dir1
        val file_type from file_type1
        val files_used from files_used1
        val archive_dir from archive_dir1
        val s3_archive_dir from s3_archive_dir1
        val collection_type from collection_type1
        val checkpath from checkpath1
        val urlzip from urlzip1
        val url from url1
        val given_name from given_name1
        val input_name from input_name1

    output:
        file('*.file')  optional true into failedFile
        file('*.url')   optional true into failedUrl
        val(lastVal)  into successInitialCheck
    
    shell:
        lastVal = "successInitialCheck.${params.attempt}"
        '''
          #!/usr/bin/env perl
          use strict;
          use File::Basename;
          use Getopt::Long;
          use Pod::Usage;
          use Data::Dumper;
          use File::Copy;
          use File::Path qw( make_path );
          use File::Compare;

          my $run_dir = "!{params.run_dir}";
          my $profile = "!{params.profile}";
          my @collection = (!{collection});
          my @file_name = (!{file_name});
          my @file_dir = (!{file_dir});
          my @file_type = (!{file_type});
          my @files_used = (!{files_used});
          my @archive_dir = (!{archive_dir});
          my @s3_archive_dir = (!{s3_archive_dir});
          my @collection_type = (!{collection_type});

          my %passHash;        ## Keep record of completed operation
          my @failArray=();    ## pass id's to createCollection process
          my $s3upload_dir;
          
          ############ Collection Data Check ############
          for ( my $i = 0 ; $i <= $#file_name ; $i++ ) {
            my $collection      = $collection[$i];
            my $input_dir       = "$run_dir/inputs/$collection";
            $s3upload_dir       = "$input_dir/.s3up";
            my $fileType        = $file_type[$i];
            my $archiveDir      = trim( $archive_dir[$i] );
            my $s3_archiveDir   = trim( $s3_archive_dir[$i] );
            my @fileAr          = split( / \\| /, $files_used[$i], -1 );
            my $inputDirCheck   = "false";
            my $archiveDirCheck = "false";
            my $s3_archiveDirCheck = "";
            my $inputFile       = "";
            my $inputFile1      = "";
            my $inputFile2      = "";
            my $archFile        = "";
            my $archFile1       = "";
            my $archFile2       = "";
            
            if ( !-d $input_dir ) {
                runCommand("mkdir -p $input_dir");
                if ( !-d $input_dir ) { die "Failed to create input_dir: $input_dir"; }
            }
          
            ## first check input folder, archive_dir and s3_archivedir for expected files
            if ( $collection_type[$i] eq "single" ) {
              $inputFile = "$input_dir/$file_name[$i].$fileType";
              if ( checkFile($inputFile) && checkFile("$input_dir/.success_$file_name[$i]")) {
                $inputDirCheck = "true";
              } else {
                runCommand("rm -f $inputFile $input_dir/.success_$file_name[$i]");
              }
            }
            elsif ( $collection_type[$i] eq "pair" ) {
              $inputFile1                  = "$input_dir/$file_name[$i].R1.$fileType";
              $inputFile2                  = "$input_dir/$file_name[$i].R2.$fileType";
              if ( checkFile($inputFile1) && checkFile($inputFile2) && checkFile("$input_dir/.success_$file_name[$i]")) {
                $inputDirCheck = "true";
              } else {
                runCommand("rm -f $inputFile1 $inputFile2 $input_dir/.success_$file_name[$i]");
              }
            }
            if ( $s3_archiveDir ne "" ) {
                my @s3_archiveDirData = split( /	/, $s3_archiveDir);
                my $s3Path = $s3_archiveDirData[0]; 
                my $confID = $s3_archiveDirData[1];
                if ( $collection_type[$i] eq "single" ) {
                $archFile = "$s3Path/$file_name[$i].$fileType";
                if ( checkS3File("$archFile.gz", $confID) && checkS3File("$archFile.gz.count", $confID) && checkS3File("$archFile.gz.md5sum", $confID)) {
                    $s3_archiveDirCheck = "true";
                } else {
                    $s3_archiveDirCheck = "false";
                }
              }
              elsif ( $collection_type[$i] eq "pair" ) {
                $archFile1 = "$s3Path/$file_name[$i].R1.$fileType";
                $archFile2 = "$s3Path/$file_name[$i].R2.$fileType";
                if ( checkS3File("$archFile1.gz", $confID) && checkS3File("$archFile1.gz.count", $confID) && checkS3File("$archFile1.gz.md5sum", $confID) && checkS3File("$archFile2.gz",$confID) && checkS3File("$archFile2.gz.count",$confID) && checkS3File("$archFile2.gz.md5sum",$confID)) {
                    $s3_archiveDirCheck = "true";
                } else {
                    $s3_archiveDirCheck = "false";
                }
              }
            }
            ## if s3_archiveDirCheck is false (not '') and $archiveDir eq "" then act as if $archiveDir defined as s3upload_dir
            ## for s3 upload first archive files need to be prepared. 
            ## If $archiveDir is not empty then copy these files to $s3upload_dir.
            ## else $archiveDir is empty create archive files in $s3upload_dir.
            if ( $archiveDir eq "" && $s3_archiveDirCheck eq "false") {
                $archiveDir = "$s3upload_dir";
            }

            if ( $archiveDir ne "" ) {
              if ( !-d $archiveDir ) {
                runCommand("mkdir -p $archiveDir");
                if ( !-d $archiveDir ) { die "Failed to create archiveDir: $archiveDir"; }
              }
              if ( $collection_type[$i] eq "single" ) {
                $archFile = "$archiveDir/$file_name[$i].$fileType";
                if ( checkFile("$archFile.gz") && checkFile("$archFile.gz.count")) {
                  $archiveDirCheck = "true";
                } elsif ( checkFile("$archFile.gz") || checkFile("$archFile.gz.count") ) {
                  ## if only one of them exist then remove files
                  runCommand("rm -f $archFile.gz");
                }
              }
              elsif ( $collection_type[$i] eq "pair" ) {
                $archFile1 = "$archiveDir/$file_name[$i].R1.$fileType";
                $archFile2 = "$archiveDir/$file_name[$i].R2.$fileType";
                if ( checkFile("$archFile1.gz") && checkFile("$archFile1.gz.count") && checkFile("$archFile2.gz") && checkFile("$archFile2.gz.count") ) {
                  $archiveDirCheck = "true";
                } elsif ( checkFile("$archFile1.gz") || checkFile("$archFile2.gz") ) {
                  ## if only one of them exist then remove files
                  runCommand("rm -f $archFile1.gz $archFile2.gz");
                }
              }
            }

            if (   $inputDirCheck eq "true" && $archiveDirCheck eq "false" && $archiveDir ne "" ){
              ## remove inputDir files and cleanstart
              if ( $collection_type[$i] eq "single" ) {
                runCommand("rm $inputFile");
              }
              elsif ( $collection_type[$i] eq "pair" ) {
                runCommand("rm $inputFile1");
                runCommand("rm $inputFile2");
              }
              $inputDirCheck = "false";
            }


            print "inputDirCheck for $file_name[$i]: $inputDirCheck\\n";
            print "archiveDirCheck for $file_name[$i]: $archiveDirCheck\\n";
            print "archiveDir for $file_name[$i]: $archiveDir\\n";
            print "s3_archiveDirCheck for $file_name[$i]: $s3_archiveDirCheck\\n";


            if ( $inputDirCheck eq "false" && $archiveDirCheck eq "true" ) {
                failedCheck($i);
            }
            elsif ( $inputDirCheck eq "false" && $archiveDirCheck eq "false" && $s3_archiveDirCheck eq "true" && $profile eq "amazon") {
                failedCheck($i);
            }
            elsif ( $inputDirCheck eq "false" && $archiveDirCheck eq "false" ) {
                failedCheck($i);
            }
            elsif ($inputDirCheck eq "true" && $archiveDirCheck eq "false" && $archiveDir eq "" ){
                $passHash{ $file_name[$i] } = "passed";
            }
            elsif ( $inputDirCheck eq "true" && $archiveDirCheck eq "true" ) {
                if ($s3_archiveDirCheck eq "false"){
                    failedCheck($i);
                } else {
                    $passHash{ $file_name[$i] } = "passed";
                }
            }
          } 
          
          ############ Collection Check Summary ############
          print "\\nCollection Check Summary:\\n";
          for ( my $k = 0 ; $k <= $#file_name ; $k++ ) {
            if ( $passHash{ $file_name[$k] } eq "passed" ){
                print "FileCheckSuccess: $file_name[$k]\\n";
            } else {
                print "FileCheckFailed: $file_name[$k]\\n";
            }
          }
          
          ############ Url Data Check ############
          
          my @checkpath = (!{checkpath});
          my @urlzip = (!{urlzip});
          my @url = (!{url});
          my @given_name = (!{given_name});
          my @input_name = (!{input_name});

          my %passHashUrl;        ## Keep record of exist urls
          my @failArrayUrl=();    ## pass id's to downloadUrl process

          for ( my $u = 0 ; $u <= $#given_name ; $u++ ) {
            my $checkpath       = $checkpath[$u];
            my $urlzip          = $urlzip[$u];
            my $url             = $url[$u];
            my $given_name      = $given_name[$u];
            my $input_name      = $input_name[$u];
            
            ## first check input_name, or $checkpath (if defined) for expected files
            ## if item ends with slash(/) then check if directory is exist
            my $target = $input_name;
            my $target_type = ""; ## dir or file
            if ( $checkpath ne "" ) {
                $target = $checkpath;
            }
            if (checkFileDir($target)){
                $passHashUrl{ $given_name } = "passed";
            } else {
                failedCheckUrl($u);
            }
          }

          ############ Url Check Summary ############
          print "\\nUrl Check Summary:\\n";
          for ( my $y = 0 ; $y <= $#given_name ; $y++ ) {
            if ( $passHashUrl{ $given_name[$y] } eq "passed" ){
                print "UrlCheckSuccess: $given_name[$y]\\n";
            } else {
                print "UrlCheckFailed: $given_name[$y]\\n";
            }
          }


          ##Subroutines

          sub runCommand {
            my ($com) = @_;
            my $error = system($com);
            if   ($error) { die "Command failed: $error $com\\n"; }
            else          { print "Command successful: $com\\n"; }
          }

          sub checkFile {
            my ($file) = @_;
            print "checkFile: $file\\n";
            return 1 if ( -e $file );
            return 0;
          }
          
          sub checkFileDir {
            my ($fileDir) = @_;
            print "checkFileDir: $fileDir\\n";
            chomp($fileDir);
            my $lstChr = substr($fileDir, -1);
            if ($lstChr eq '/'){
                my $target_type = "dir";
                return 1 if ( -d $fileDir );
                return 0;
            } else {
                my $target_type = "file";
                return 1 if ( -e $fileDir );
                return 0;
            }
          }

          sub checkS3File{
            my ( $file, $confID) = @_;
            my $tmpSufx = $file;
            $tmpSufx =~ s/[^A-Za-z0-9]/_/g;
            runCommand ("mkdir -p $s3upload_dir && > $s3upload_dir/.info.$tmpSufx ");
            my $err = system ("s3cmd info --config=$run_dir/initialrun/.conf.$confID $file >$s3upload_dir/.info.$tmpSufx 2>&1 ");
            ## if file not found then it will give error
            my $checkMD5 = 'false';
            if ($err){
                print "S3File Not Found: $file\\n";
                return 0;
            } else {
                open(FILE,"$s3upload_dir/.info.$tmpSufx");
                if (grep{/MD5/} <FILE>){
                    $checkMD5 = 'true';
                }
                close FILE;
            }
            return 1 if ( $checkMD5 eq 'true' );
            print "S3File Not Found: $file\\n";
            return 0;
          }
          
          sub failedCheck{
            my ($id) = @_;
            push(@failArray, $id) unless grep{$_ eq $id} @failArray;
            runCommand("touch $id.file");
          }
          sub failedCheckUrl{
            my ($id) = @_;
            push(@failArrayUrl, $id) unless grep{$_ eq $id} @failArrayUrl;
            runCommand("touch $id.url");
          }
          
          sub trim {
            my $s = shift;
            $s =~ s/^\\s+|\\s+$//g;
            return $s;
          }
        '''
}


process downloadUrl {

    input:
        each failedUrl from failedUrl.flatten()
        val checkpath from checkpath2
        val urlzip from urlzip2
        val url from url2
        val given_name from given_name2
        val input_name from input_name2

    output:
        val("success.${params.attempt}")  into successUrl
    
    shell:
        fileID = failedUrl.baseName
        given_name_list = quo2Lst(given_name)
        println "INFO: Downloading missing files for parameter: "+given_name_list[fileID.toInteger()]
        '''
        #!/usr/bin/env perl
        use strict;
        use File::Basename;
        use Getopt::Long;
        use Pod::Usage;
        use Data::Dumper;
        use File::Copy;
        use File::Path qw( make_path );
        use File::Compare;
            
        ############ Url Data Check ############
          
        my @checkpath = (!{checkpath});
        my @urlzip = (!{urlzip});
        my @url = (!{url});
        my @given_name = (!{given_name});
        my @input_name = (!{input_name});

        my %passHashUrl;        ## Keep record of exist urls
        my @failArrayUrl=();    ## pass id's to downloadUrl process

        my $u = !{fileID}; 
        my $checkpath       = $checkpath[$u];
        my $urlzip          = $urlzip[$u];
        my $url             = $url[$u];
        my $given_name      = $given_name[$u];
        my $input_name      = $input_name[$u];
            
        ## first check input_name, or $checkpath (if defined) for expected files
        ## if item ends with slash(/) then check if directory is exist
        my $target = $input_name;
        my $target_type = ""; ## dir or file
        my $target_path = ""; ## directory to download
        my $check       = $target; ## used in checkFileDir()
        if ( $checkpath ne "" ) {
            $check = $checkpath;
        }
        $target_type = getFileDirType($target);
        $target_path = dirname($target);
        
        print "target_path: $target_path\\n";
        if (checkFileDir($check)){
            $passHashUrl{ $given_name } = "passed";
        } else {
            if ($url ne ""){
                my $url_type = getFileDirType($url);
                runWget ($url, $target_path, $url_type);
            } elsif ($urlzip ne ""){
                my $url_type = getFileDirType($urlzip);
                runWget ($urlzip, $target_path, $url_type);
                if ($urlzip =~ /\\.tar.gz\\z/){
                    my $zippedFile = basename($urlzip);
                    ## --strip-components 1 to remove top directory after extraction 
                    runCommand ("tar xfz  $target_path/$zippedFile --strip-components 1 -C $target_path/ && rm -f $target_path/$zippedFile");
                }
            }
            $passHashUrl{ $given_name } = "passed";
        }
          
        ############ Url Check Summary ############
        print "\\nUrl Check Summary:\\n";
        for ( my $y = 0 ; $y <= $#given_name ; $y++ ) {
            if ( $passHashUrl{ $given_name[$y] } eq "passed" ){
                print "UrlCheckSuccess: $given_name[$y]\\n";
            } else {
                print "UrlCheckFailed: $given_name[$y]\\n";
            }
        }


        ##Subroutines

        sub runCommand {
            my ($com) = @_;
            my $error = system($com);
            if   ($error) { die "Command failed: $error $com\\n"; }
            else          { print "Command successful: $com\\n"; }
        }

        sub checkFile {
            my ($file) = @_;
            print "checkFile: $file\\n";
            return 1 if ( -e $file );
            return 0;
        }
          
        sub checkFileDir {
            my ($fileDir) = @_;
            print "checkFileDir: $fileDir\\n";
            chomp($fileDir);
            my $lstChr = substr($fileDir, -1);
            print "last: $lstChr\\n";
            if ($lstChr eq '/'){
                my $target_type = "dir";
                print "target_type: $target_type\\n";
                return 1 if ( -d $fileDir );
                return 0;
            } else {
                my $target_type = "file";
                print "target_type: $target_type\\n";
                return 1 if ( -e $fileDir );
                return 0;
            }
        }
        
        sub getFileDirType {
            my ($target) = @_;
            chomp($target);
            my $lstChr = substr($target, -1);
            if ($lstChr eq '/'){
                $target_type = "dir";
            } else {
                $target_type = "file";
            }
            return $target_type;
        }
        
        sub runWget {
            my ( $url,$target_path, $url_type) = @_;
            runCommand ("mkdir -p $target_path");
            ## -r,  --recursive
            ## -N,  --timestamping 
            ## -nc for not downloading downloaded items (there is no corruption check!)
            ## --mirror: equivalent to ‘-r -N -l inf --no-remove-listing’.
            ## -nH: --no-host-directories
            ## get cut-dirs (removed extra sub dirs) based on given url:(eg.https://galaxyweb/test/)
            ## Both --no-clobber and -N cannot be used at the same time.
            
            my $slashCount = () = $url =~ /\\//g;
            my $cutDir =$slashCount - 3;
            if ($url_type eq "dir"){
                runCommand ("wget -l inf -nc -nH --cut-dirs=$cutDir -R 'index.html*' -r --no-parent --directory-prefix=$target_path $url");
            } elsif ($url_type eq "file"){
                runCommand ("wget -l inf -nc -nH --cut-dirs=$cutDir -R 'index.html*' --directory-prefix=$target_path $url");
            }
            
        }
        
        '''
}

process createCollection {
    errorStrategy 'retry'
    maxRetries 2

    input:
        each failedFile from failedFile.flatten()
        val collection from collection2
        val file_name from file_name2
        val file_dir from file_dir2
        val file_type from file_type2
        val files_used from files_used2
        val archive_dir from archive_dir2
        val s3_archive_dir from s3_archive_dir2
        val collection_type from collection_type2

    output:
        val("success.${params.attempt}")  into success
    
    shell:
        fileID = failedFile.baseName
        '''
            #!/usr/bin/env perl
            use strict;
            use File::Basename;
            use Getopt::Long;
            use Pod::Usage;
            use Data::Dumper;
            use File::Copy;
            use File::Path qw( make_path );
            use File::Compare;

            my $run_dir = "!{params.run_dir}";
            my $profile = "!{params.profile}";
            my @collection = (!{collection});
            my @file_name = (!{file_name});
            my @file_dir = (!{file_dir});
            my @file_type = (!{file_type});
            my @files_used = (!{files_used});
            my @archive_dir = (!{archive_dir});
            my @s3_archive_dir = (!{s3_archive_dir});
            my @collection_type = (!{collection_type});
            my %passHash;    ## Keep record of completed operation
            my %validInputHash; ## Keep record of files as fullpath
          
            my $i = !{fileID};
            my $collection         = $collection[$i];
            my $input_dir          = "$run_dir/inputs/$collection";
            my $s3down_dir_prefix  = "$input_dir/.tmp";
            my $urldown_dir_prefix = "$input_dir/.tmpUrl";
            my $s3upload_dir       = "$input_dir/.s3up";
            my $fileType        = $file_type[$i];
            my $archiveDir      = trim( $archive_dir[$i] );
            my $s3_archiveDir   = trim( $s3_archive_dir[$i] );
            my @fileAr          = split( / \\| /, $files_used[$i], -1 );
            my @fullfileAr      = ();
            my @fullfileArR1    = ();
            my @fullfileArR2    = ();
            my $inputDirCheck   = "false";
            my $archiveDirCheck = "false";
            my $s3_archiveDirCheck = "";
            my $inputFile       = "";
            my $inputFile1      = "";
            my $inputFile2      = "";
            my $archFile        = "";
            my $archFile1       = "";
            my $archFile2       = "";

            if ( !-d $input_dir ) {
                runCommand("mkdir -p $input_dir");
                if ( !-d $input_dir ) { die "Failed to create input_dir: $input_dir"; }
            }
            
            ## first check input folder, archive_dir and s3_archivedir for expected files
            if ( $collection_type[$i] eq "single" ) {
              $inputFile = "$input_dir/$file_name[$i].$fileType";
              if ( checkFile($inputFile) && checkFile("$input_dir/.success_$file_name[$i]")) {
                $inputDirCheck = "true";
              } else {
                runCommand("rm -f $inputFile $input_dir/.success_$file_name[$i]");
              }
            }
            elsif ( $collection_type[$i] eq "pair" ) {
              $inputFile1                  = "$input_dir/$file_name[$i].R1.$fileType";
              $inputFile2                  = "$input_dir/$file_name[$i].R2.$fileType";
              if ( checkFile($inputFile1) && checkFile($inputFile2) && checkFile("$input_dir/.success_$file_name[$i]")) {
                $inputDirCheck = "true";
              } else {
                runCommand("rm -f $inputFile1 $inputFile2 $input_dir/.success_$file_name[$i]");
              }
            }
            if ( $s3_archiveDir ne "" ) {
                my @s3_archiveDirData = split( /	/, $s3_archiveDir);
                my $s3Path = $s3_archiveDirData[0]; 
                my $confID = $s3_archiveDirData[1];
                if ( $collection_type[$i] eq "single" ) {
                $archFile = "$s3Path/$file_name[$i].$fileType";
                if ( checkS3File("$archFile.gz", $confID) && checkS3File("$archFile.gz.count", $confID) && checkS3File("$archFile.gz.md5sum", $confID)) {
                    $s3_archiveDirCheck = "true";
                } else {
                    $s3_archiveDirCheck = "false";
                }
              }
              elsif ( $collection_type[$i] eq "pair" ) {
                $archFile1 = "$s3Path/$file_name[$i].R1.$fileType";
                $archFile2 = "$s3Path/$file_name[$i].R2.$fileType";
                if ( checkS3File("$archFile1.gz", $confID) && checkS3File("$archFile1.gz.count", $confID) && checkS3File("$archFile1.gz.md5sum", $confID) && checkS3File("$archFile2.gz",$confID) && checkS3File("$archFile2.gz.count",$confID) && checkS3File("$archFile2.gz.md5sum",$confID)) {
                    $s3_archiveDirCheck = "true";
                } else {
                    $s3_archiveDirCheck = "false";
                }
              }
            }
            ## if s3_archiveDirCheck is false (not '') and $archiveDir eq "" then act as if $archiveDir defined as s3upload_dir
            ## for s3 upload first archive files need to be prepared. 
            ## If $archiveDir is not empty then copy these files to $s3upload_dir.
            ## else $archiveDir is empty create archive files in $s3upload_dir.
            if ( $archiveDir eq "" && $s3_archiveDirCheck eq "false") {
                $archiveDir = "$s3upload_dir";
            }

            if ( $archiveDir ne "" ) {
              if ( !-d $archiveDir ) {
                runCommand("mkdir -p $archiveDir");
                if ( !-d $archiveDir ) {die "Failed to create archiveDir: $archiveDir";}
              }
              if ( $collection_type[$i] eq "single" ) {
                $archFile = "$archiveDir/$file_name[$i].$fileType";
                if ( checkFile("$archFile.gz") && checkFile("$archFile.gz.count")) {
                  $archiveDirCheck = "true";
                } elsif ( checkFile("$archFile.gz") || checkFile("$archFile.gz.count") ) {
                  ## if only one of them exist then remove files
                  runCommand("rm -f $archFile.gz");
                }
              }
              elsif ( $collection_type[$i] eq "pair" ) {
                $archFile1 = "$archiveDir/$file_name[$i].R1.$fileType";
                $archFile2 = "$archiveDir/$file_name[$i].R2.$fileType";
                if ( checkFile("$archFile1.gz") && checkFile("$archFile1.gz.count") && checkFile("$archFile2.gz") && checkFile("$archFile2.gz.count") ) {
                  $archiveDirCheck = "true";
                } elsif ( checkFile("$archFile1.gz") || checkFile("$archFile2.gz") ) {
                  ## if only one of them exist then remove files
                  runCommand("rm -f $archFile1.gz $archFile2.gz");
                }
              }
            }

            print "inputDirCheck for $file_name[$i]: $inputDirCheck\\n";
            print "archiveDirCheck for $file_name[$i]: $archiveDirCheck\\n";
            print "archiveDir for $file_name[$i]: $archiveDir\\n";
            print "s3_archiveDirCheck for $file_name[$i]: $s3_archiveDirCheck\\n";

            if (   $inputDirCheck eq "true" && $archiveDirCheck eq "false" && $archiveDir ne "" ){
              ## remove inputDir files and cleanstart
              if ( $collection_type[$i] eq "single" ) {
                runCommand("rm $inputFile");
              }
              elsif ( $collection_type[$i] eq "pair" ) {
                runCommand("rm $inputFile1");
                runCommand("rm $inputFile2");
              }
              $inputDirCheck = "false";
            }

            if ( $inputDirCheck eq "false" && $archiveDirCheck eq "true" ) {
                if ( $collection_type[$i] eq "single" ) {
                    arch2Input ("$archFile.gz", "$inputFile.gz", $s3_archiveDirCheck, $s3_archiveDir);
                } elsif ( $collection_type[$i] eq "pair" ) {
                    arch2Input ("$archFile1.gz", "$inputFile1.gz", $s3_archiveDirCheck, $s3_archiveDir);
                    arch2Input ("$archFile2.gz", "$inputFile2.gz", $s3_archiveDirCheck, $s3_archiveDir);
                }
                runCommand("touch $input_dir/.success_$file_name[$i]");
                $passHash{ $file_name[$i] } = "passed";
            }
            ## if $s3_archiveDirCheck eq "true" && $archiveDirCheck eq "false" && $profile eq "amazon": no need to check input file existance. Download s3 file and call it archived file.
            elsif ( $inputDirCheck eq "false" && $archiveDirCheck eq "false" && $s3_archiveDirCheck eq "true" && $profile eq "amazon") {
                if ( $collection_type[$i] eq "single" ) {
                    my $s3tmp_dir_sufx = s3downCheck($s3_archiveDir, "$file_name[$i].$fileType.gz");
                    my $archFile = $s3tmp_dir_sufx . "/" . "$file_name[$i].$fileType";
                    arch2Input ("$archFile.gz", "$inputFile.gz", $s3_archiveDirCheck, $s3_archiveDir);
                } elsif ( $collection_type[$i] eq "pair" ) {
                    my $s3tmp_dir_sufx1 = s3downCheck($s3_archiveDir, "$file_name[$i].R1.$fileType.gz");
                    my $archFile1 = $s3tmp_dir_sufx1 . "/" . "$file_name[$i].R1.$fileType";
                    my $s3tmp_dir_sufx2 = s3downCheck($s3_archiveDir, "$file_name[$i].R2.$fileType.gz");
                    my $archFile2 = $s3tmp_dir_sufx2 . "/" . "$file_name[$i].R2.$fileType";
                    arch2Input ("$archFile1.gz", "$inputFile1.gz", $s3_archiveDirCheck, $s3_archiveDir);
                    arch2Input ("$archFile2.gz", "$inputFile2.gz", $s3_archiveDirCheck, $s3_archiveDir);
                }
                runCommand("touch $input_dir/.success_$file_name[$i]");
                $passHash{ $file_name[$i] } = "passed";
            }
            ##create new collection files
            elsif ( $inputDirCheck eq "false" && $archiveDirCheck eq "false" ) {
              ##Keep full path of files that needs to merge in @fullfileAr
              for ( my $k = 0 ; $k <= $#fileAr ; $k++ ) {
                if ( $collection_type[$i] eq "single" ) {
                  ## for GEO files: file_dir will be empty so @fullfileAr will be empty.
                  if ($file_dir[$i] =~ m/s3:/i ){
                    my $s3tmp_dir_sufx = s3downCheck($file_dir[$i], $fileAr[$k]);
                    push @fullfileAr, $s3tmp_dir_sufx . "/" . $fileAr[$k];
                  
                  } elsif (($file_dir[$i] =~ m/http:/i ) || ($file_dir[$i] =~ m/https:/i ) || ($file_dir[$i] =~ m/ftp:/i )){
                    my $urltmp_dir_sufx = urldown("$file_dir[$i]/$fileAr[$k]", $fileAr[$k]);
                    push @fullfileAr, $urltmp_dir_sufx . "/" . $fileAr[$k];
                  
                  } elsif (trim( $file_dir[$i] ne "")){
                    push @fullfileAr, $file_dir[$i] . "/" . $fileAr[$k];
                  }
                }
                elsif ( $collection_type[$i] eq "pair" ) {
                  if ($file_dir[$i] =~ m/s3:/i ){
                    my @pair = split( /,/, $fileAr[$k], -1 );
                    my $s3tmp_dir_sufx1 = s3downCheck($file_dir[$i], $pair[0]);
                    my $s3tmp_dir_sufx2 = s3downCheck($file_dir[$i], $pair[1]);
                    print "$s3tmp_dir_sufx1\\n";
                    push @fullfileArR1, $s3tmp_dir_sufx1 . "/" . $pair[0];
                    push @fullfileArR2, $s3tmp_dir_sufx2 . "/" . $pair[1];
                  
                  } elsif (($file_dir[$i] =~ m/http:/i ) || ($file_dir[$i] =~ m/https:/i ) || ($file_dir[$i] =~ m/ftp:/i )){
                    my @pair = split( /,/, $fileAr[$k], -1 );
                    my $urltmp_dir_sufx1 = urldown("$file_dir[$i]/$pair[0]", $pair[0]);
                    my $urltmp_dir_sufx2 = urldown("$file_dir[$i]/$pair[1]", $pair[1]);
                    print "$urltmp_dir_sufx1\\n";
                    push @fullfileArR1, $urltmp_dir_sufx1 . "/" . $pair[0];
                    push @fullfileArR2, $urltmp_dir_sufx2 . "/" . $pair[1];
                  
                  } elsif (trim( $file_dir[$i] ne "")){
                    my @pair = split( /,/, $fileAr[$k], -1 );
                    push @fullfileArR1, $file_dir[$i] . "/" . $pair[0];
                    push @fullfileArR2, $file_dir[$i] . "/" . $pair[1];
                  }
                }
              }
              if ( $archiveDir ne "") {
                ##merge files in archive dir then copy to inputdir
                my $cat = "cat";
                ##Don't run mergeGzip for GEO files
                if (scalar @fullfileAr != 0 && $collection_type[$i] eq "single"){
                  my $filestr = join( ' ', @fullfileAr );
                  $cat = "zcat -f" if ( $filestr =~ /\\.gz/ );
                  mergeGzipCountMd5sum( $cat, $filestr, $archFile );
                } elsif ( scalar @fullfileArR1 != 0 && $collection_type[$i] eq "pair" ) {
                  my $filestrR1 = join( ' ', @fullfileArR1 );
                  my $filestrR2 = join( ' ', @fullfileArR2 );
                  $cat = "zcat -f" if ( $filestrR1 =~ /\\.gz/ );
                  mergeGzipCountMd5sum( $cat, $filestrR1, $archFile1 );
                  mergeGzipCountMd5sum( $cat, $filestrR2, $archFile2 );
                } else {
                  ##Run fastqdump and CountMd5sum for GEO files
                  my $gzip = "--gzip";
                  if ( $collection_type[$i] eq "single" ) {
                    fasterqDump($gzip, $archiveDir, $fileAr[0], $file_name[$i], $collection_type[$i]);
                    countMd5sum("$archFile");
                  }
                  elsif ( $collection_type[$i] eq "pair" ) {
                    fasterqDump($gzip, $archiveDir, $fileAr[0], $file_name[$i], $collection_type[$i]);
                    countMd5sum("$archFile1");
                    countMd5sum("$archFile2");
                  }
                }
                if ( $collection_type[$i] eq "single" ) {
                    arch2Input ("$archFile.gz", "$inputFile.gz", $s3_archiveDirCheck, $s3_archiveDir);
                }
                elsif ( $collection_type[$i] eq "pair" ) {
                    arch2Input ("$archFile1.gz", "$inputFile1.gz", $s3_archiveDirCheck, $s3_archiveDir);
                    arch2Input ("$archFile2.gz", "$inputFile2.gz", $s3_archiveDirCheck, $s3_archiveDir);
                }
              }
              else {
                ##archive_dir is not defined then merge files in input_dir
                my $cat = "cat";
                ##Don't run merge for GEO files
                if ( scalar @fullfileAr != 0 && $collection_type[$i] eq "single" ) {
                  my $filestr = join( ' ', @fullfileAr );
                  $cat = "zcat -f" if ( $filestr =~ /\\.gz/ );
                  runCommand("$cat $filestr > $inputFile");
                } elsif ( scalar @fullfileArR1 != 0 && $collection_type[$i] eq "pair" ) {
                  my $filestrR1 = join( ' ', @fullfileArR1 );
                  my $filestrR2 = join( ' ', @fullfileArR2 );
                  $cat = "zcat -f " if ( $filestrR1 =~ /\\.gz/ );
                  runCommand("$cat $filestrR1 > $inputFile1");
                  runCommand("$cat $filestrR2 > $inputFile2");
                } else {
                  ##Run fastqdump without --gzip for GEO files
                  fasterqDump("", $input_dir, $fileAr[0], $file_name[$i], $collection_type[$i]);
                }
              }
              runCommand("touch $input_dir/.success_$file_name[$i]");
              $passHash{ $file_name[$i] } = "passed";
            } 
            elsif ($inputDirCheck eq "true" && $archiveDirCheck eq "false" && $archiveDir eq "" ){
              $passHash{ $file_name[$i] } = "passed";
            } 
            elsif ( $inputDirCheck eq "true" && $archiveDirCheck eq "true" ) {
                if ($s3_archiveDirCheck eq "false"){
                    if ( $collection_type[$i] eq "single" ) {
                        prepS3Upload ("$archFile.gz", "$archFile.gz.count", "$archFile.gz.md5sum", $s3_archiveDir);
                    }
                    elsif ( $collection_type[$i] eq "pair" ) {
                        prepS3Upload ("$archFile1.gz", "$archFile1.gz.count", "$archFile1.gz.md5sum", $s3_archiveDir);
                        prepS3Upload ("$archFile2.gz", "$archFile2.gz.count", "$archFile2.gz.md5sum", $s3_archiveDir);
                    }
                }
              $passHash{ $file_name[$i] } = "passed";
            }


            die "Error 64: please check your input file:$file_name[$i]"
            unless ( $passHash{ $file_name[$i] } eq "passed" );


          ##Subroutines

          sub runCommand {
            my ($com) = @_;
            my $error = system($com);
            if   ($error) { die "Command failed: $error $com\\n"; }
            else          { print "Command successful: $com\\n"; }
          }

          sub checkFile {
            my ($file) = @_;
            print "$file\\n";
            return 1 if ( -e $file );
            return 0;
          }

          sub checkS3File{
            my ( $file, $confID) = @_;
            my $tmpSufx = $file;
            $tmpSufx =~ s/[^A-Za-z0-9]/_/g;
            runCommand ("mkdir -p $s3upload_dir && > $s3upload_dir/.info.$tmpSufx ");
            my $err = system ("s3cmd info --config=$run_dir/initialrun/.conf.$confID $file >$s3upload_dir/.info.$tmpSufx 2>&1 ");
            ## if file not found then it will give error
            my $checkMD5 = 'false';
            if ($err){
                print "S3File Not Found: $file\\n";
                return 0;
            } else {
                open(FILE,"$s3upload_dir/.info.$tmpSufx");
                if (grep{/MD5/} <FILE>){
                    $checkMD5 = 'true';
                }
                close FILE;
            }
            return 1 if ( $checkMD5 eq 'true' );
            print "S3File Not Found: $file\\n";
            return 0;
          }

          sub makeS3Bucket{
            my ( $bucket, $confID) = @_;
            my $err = system ("s3cmd info --config=$run_dir/initialrun/.conf.$confID $bucket 2>&1 ");
            ## if bucket is not found then it will give error
            my $check = 'false';
            if ($err){
                print "S3bucket Not Found: $bucket\\n";
                runCommand("s3cmd mb --config=$run_dir/initialrun/.conf.$confID $bucket ");
            } 
          }

          sub trim {
            my $s = shift;
            $s =~ s/^\\s+|\\s+$//g;
            return $s;
          }

          sub copyFile {
            my ( $file, $target ) = @_;
            runCommand("rsync -vazu $file $target");
          }



          sub countMd5sum {
            my ($inputFile ) = @_;
            runCommand("s=\\$(zcat $inputFile.gz|wc -l) && echo \\$((\\$s/4)) > $inputFile.gz.count && md5sum $inputFile.gz > $inputFile.gz.md5sum");
          }

          sub mergeGzipCountMd5sum {
            my ( $cat, $filestr, $inputFile ) = @_;
            runCommand("$cat $filestr > $inputFile && gzip $inputFile");
            countMd5sum($inputFile);
          }

          sub parseMd5sum{
            my ( $path )  = @_;
            open my $file, '<', $path; 
            my $firstLine = <$file>; 
            close $file;
            my @arr = split(' ', $firstLine);
            my $md5sum = $arr[0];
            return $md5sum;
          }



          sub md5sumCompare{
            my ( $path1, $path2) = @_;
            my $md5sum1 = parseMd5sum($path1);
            my $md5sum2 = parseMd5sum($path2);
            if ($md5sum1 eq $md5sum2 && $md5sum1 ne ""){
                print "MD5sum check successful for $path1 vs $path2: $md5sum1 vs $md5sum2 \\n";
                return 'true';
            } else {
                print "MD5sum check failed for $path1 vs $path2: $md5sum1 vs $md5sum2 \\n";
                return 'false';
            }
          }

          sub S3UploadCheck{
            my ( $localpath, $archFileMd5sum, $s3Path, $confID, $upload_path) = @_;
              my $file = basename($localpath);  
              runCommand("mkdir -p $upload_path/$file.chkS3Up && cd $upload_path/$file.chkS3Up && s3cmd get --force --config=$upload_path/.conf.$confID $s3Path/$file");
              runCommand("cd $upload_path/$file.chkS3Up && md5sum $upload_path/$file.chkS3Up/$file > $upload_path/$file.chkS3Up/$file.md5sum  ");
              if (md5sumCompare($archFileMd5sum, "$upload_path/$file.chkS3Up/$file.md5sum") eq 'true') {
                 runCommand("rm -f ${s3upload_dir}/.s3fail_$file && touch ${s3upload_dir}/.s3success_$file");
                 return 'true';
              } else {
                 runCommand("rm -f ${s3upload_dir}/.s3success_$file && touch ${s3upload_dir}/.s3fail_$file");
                 return 'false';
              }

          }

          sub S3Upload{
          my ( $path, $bucketName, $s3PathRest, $confID, $upload_path) = @_;
              my $file = basename($path); 
              my $md5sumCmd = "openssl md5 -binary $path | base64";
              runCommand($md5sumCmd); ## to check that cmd is working.
              my $md5checksum = `$md5sumCmd`;
              chomp($md5checksum);
              print "file to upload: $path\\n";
              print "bucketName: $bucketName\\n";
              print "s3PathRest: $s3PathRest\\n";
              print "md5checksum: $md5checksum\\n";
              runCommand("export \\$(grep access_key= $upload_path/.conf.$confID | sed 's/access_key=/AWS_ACCESS_KEY_ID=/') && export \\$(grep secret_key= $upload_path/.conf.$confID | sed 's/secret_key=/AWS_SECRET_ACCESS_KEY=/') && aws s3api put-object --bucket $bucketName --key $s3PathRest/$file --body $path --content-md5 $md5checksum");
              ## aws s3api put-object --bucket biocorebackup --key dolphinnext_test/markers.tsv --body /mac/markers.tsv --content-md5 uG2ZAIskslzypnhn+xCiZA== --metadata md5checksum=uG2ZAIskslzypnhn+xCiZA==
          }

          sub prepS3Upload{
          my ( $archFile, $archFileCount, $archFileMd5sum, $s3_archiveDir ) = @_;
            my @data = split( /	/, $s3_archiveDir);
            my $s3Path = $data[0]; 
            my $confID = $data[1];
            my $upload_path = ${s3upload_dir};
            print "upload_path: $upload_path\\n";
            runCommand("mkdir -p $upload_path && cd $upload_path && rsync -vazu $archFile $archFileCount $archFileMd5sum . && rsync -vazu $run_dir/initialrun/.conf.$confID . ");
            my $bucket=$s3Path;
            $bucket=~ s/(s3:\\/\\/)|(S3:\\/\\/)//;
            my @arr = split('/', $bucket);
            my $bucketName = shift @arr;
            $bucket = 's3://'.$bucketName;
            my $s3PathRest = join '/', @arr;
            ##make bucket if not exist
            makeS3Bucket($bucket, $confID);
            S3Upload($archFile, $bucketName, $s3PathRest, $confID, $upload_path);
            S3Upload($archFileCount, $bucketName, $s3PathRest, $confID, $upload_path);
            S3Upload($archFileMd5sum, $bucketName, $s3PathRest, $confID, $upload_path);
          }

          ## copy files from achive directory to input directory and extract them in input_dir
          sub arch2Input {
            my ( $archFile, $inputFile, $s3_archiveDirCheck, $s3_archiveDir ) = @_;
            copyFile( "$archFile", "$inputFile" );
            runCommand("gunzip $inputFile");
            if ($s3_archiveDirCheck eq "false"){
                prepS3Upload("$archFile", "$archFile.count", "$archFile.md5sum", $s3_archiveDir);
            }
          }

          sub s3down {
            my ( $s3PathConf, $file_name ) = @_;
            ##first remove tmp files?
            my @data = split( /	/, $s3PathConf);
            my $s3Path = $data[0];
            my $tmpSufx = $s3Path;
            $tmpSufx =~ s/[^A-Za-z0-9]/_/g; 
            my $confID = $data[1];
            my $down_path = ${s3down_dir_prefix}.${tmpSufx};
            runCommand("mkdir -p $down_path && cd $down_path && s3cmd get --force --config=$run_dir/initialrun/.conf.$confID $s3Path/$file_name");
            print "down_path: $down_path\\n";
            return $down_path;
          }
          
          sub s3downCheck {
            my ( $s3PathConf, $file_name ) = @_;
            my $downCheck = 'false';
            my $down_path = s3down($s3PathConf, $file_name);
            ## check if md5sum is exist
            my @data = split( /	/, $s3PathConf);
            my $s3Path = $data[0];
            my $confID = $data[1];
            for ( my $c = 1 ; $c <= 3 ; $c++ ) {
                print "##s3downCheck $c started: $down_path\\n";
                my $err = system ("s3cmd info --config=$run_dir/initialrun/.conf.$confID $s3Path/$file_name.md5sum 2>&1 ");
                ## if error occurs, md5sum file is not found in s3. So md5sum-check will be skipped.
                if ($err){
                    $downCheck = 'true';
                } else {
                    ## if error not occurs, md5sum file is found in s3. So download and check md5sum.
                    my $down_path_md5 = s3down($s3PathConf, "$file_name.md5sum");
                    print "##s3downCheck down_path: $down_path\\n";
                    print "##s3downCheck down_path_md5: $down_path_md5\\n";
                    runCommand("md5sum $down_path/$file_name > $down_path/$file_name.md5sum.checkup  ");
                    if (md5sumCompare("$down_path_md5/$file_name.md5sum", "$down_path/$file_name.md5sum.checkup") eq 'true') {
                        $downCheck = 'true';
                    } else {
                        $downCheck = 'false';
                    }
                }
                if ($downCheck eq 'true'){
                    last;
                } else {
                    die "S3 download failed for 3 times. MD5sum not matched. ";
                }
            }
            return $down_path;
          }
          
          sub urldown{
            my ( $url, $file_name ) = @_;
            my $tmpSufx = $url;
            $tmpSufx =~ s/[^A-Za-z0-9]/_/g; 
            my $down_path = ${urldown_dir_prefix}.${tmpSufx};
            print "url:$url\\n";
            print "file_name:$file_name\\n";
            print "down_path:$down_path\\n";
            runWget($url,$down_path);
            return $down_path;
          }
          
          sub runWget {
            my ( $url,$target_path) = @_;
            runCommand ("mkdir -p $target_path");
            ##first remove tmp files?
            my $basename = basename($url);
            runCommand ("rm -rf $target_path/$basename");
            ## -nH: --no-host-directories
            ## get cut-dirs (removed extra sub dirs) based on given url:(eg.https://galaxyweb/test/)
            ## Both --no-clobber and -N cannot be used at the same time.
            my $slashCount = () = $url =~ /\\//g;
            my $cutDir =$slashCount - 3;
            runCommand ("wget -nH --cut-dirs=$cutDir -R 'index.html*' --directory-prefix=$target_path $url");
        }

          sub fasterqDump {
            my ( $gzip, $outDir, $srrID, $file_name,  $collection_type) = @_;
            runCommand("rm -f $outDir/${file_name}.R1.fastq $outDir/${file_name}.R2.fastq $outDir/${file_name}.fastq $outDir/${srrID}_1.fastq $outDir/${srrID}_2.fastq $outDir/${srrID} $outDir/${srrID}.fastq && mkdir -p \\\$HOME/.ncbi && mkdir -p ${outDir}/sra && echo '/repository/user/main/public/root = \\"$outDir/sra\\"' > \\\$HOME/.ncbi/user-settings.mkfg && fasterq-dump -O $outDir -t ${outDir}/sra --split-3 --skip-technical -o $srrID $srrID");
            if ($collection_type eq "pair"){
              runCommand("mv $outDir/${srrID}_1.fastq  $outDir/${file_name}.R1.fastq ");
              runCommand("mv $outDir/${srrID}_2.fastq  $outDir/${file_name}.R2.fastq ");
              if ($gzip ne ""){
                runCommand("gzip  $outDir/${file_name}.R1.fastq ");
                runCommand("gzip  $outDir/${file_name}.R2.fastq ");
              }
            } elsif ($collection_type eq "single"){
              runCommand("mv $outDir/${srrID}  $outDir/${file_name}.fastq ");
              if ($gzip ne ""){
                runCommand("gzip  $outDir/${file_name}.fastq ");
              }
            }
            runCommand("rm -f ${outDir}/sra/sra/${srrID}.sra.cache");
          }


          '''
}

process cleanUp {

    input:
        val file_name_all from file_name3
        val file_type_all from file_type3
        val collection_type_all from collection_type3
        val collection from collection3
        val successList from success.toList()
        val successInitialCheck from successInitialCheck

    output:
        file("success.${params.attempt}")  into successCleanUp
    shell:
        '''
          #!/usr/bin/env perl
          use strict;
          use File::Basename;
          use Getopt::Long;
          use Pod::Usage;
          use Data::Dumper;

          my $run_dir             = "!{params.run_dir}";
          my @file_name_all       = (!{file_name_all});
          my @collection          = (!{collection});
          my @file_type_all       = (!{file_type_all});
          my @collection_type_all = (!{collection_type_all});

          my %validInputHash; ## Keep record of files as fullpath
          my @validCollection; ## Keep record of valid Collections
          
          for ( my $i = 0 ; $i <= $#file_name_all ; $i++ ) {
            my $collection  = $collection[$i];
            push(@validCollection, "$run_dir/inputs/$collection") unless grep{$_ eq "$run_dir/inputs/$collection"} @validCollection;
            my $input_dir   = "$run_dir/inputs/$collection";
            my $fileType    = $file_type_all[$i];
            if ( $collection_type_all[$i] eq "single" ) {
              my $inputFile = "$input_dir/$file_name_all[$i].$fileType";
              $validInputHash{$inputFile} = 1;
            }
            elsif ( $collection_type_all[$i] eq "pair" ) {
              my $inputFile1                  = "$input_dir/$file_name_all[$i].R1.$fileType";
              my $inputFile2                  = "$input_dir/$file_name_all[$i].R2.$fileType";
              $validInputHash{$inputFile1} = 1;
              $validInputHash{$inputFile2} = 1;
            }
          }

          print Dumper \\\\%validInputHash;
          print Dumper \\\\@validCollection;
          
          ## remove invalid collections from inputs folder:
          my @inputCollections = <$run_dir/inputs/*>;
            foreach my $dir (@inputCollections) {
                print "dirs: $dir"; 
                if ( !grep( /^$dir$/, @validCollection)) {
                    print "Invalid directory $dir will be removed from inputs directory\\n";
                    runCommand("rm -rf $dir");
                }
            }
          
          for ( my $i = 0 ; $i <= $#validCollection ; $i++ ) {
            my $input_dir = $validCollection[$i];
            ##remove invalid files (not found in %validInputHash) from $input_dir
            my @inputDirFiles = <$input_dir/*>;
            runCommand("rm -rf $input_dir/.s3up/.conf*");
            foreach my $file (@inputDirFiles) {
                if ( !exists( $validInputHash{$file} ) ) {
                    print "Invalid file $file will be removed from input directory\\n";
                    runCommand("rm -rf $file");
                }
            }
            ## rm s3 related files
            runCommand("rm -rf $input_dir/.tmp*");
            runCommand("rm -rf $run_dir/initialrun/.conf*");
            system('touch success.!{params.attempt}');
          }

          sub runCommand {
            my ($com) = @_;
            my $error = system($com);
            if   ($error) { die "Command failed: $com\\n"; }
            else          { print "Command successful: $com\\n"; }
          }

          '''

}

workflow.onComplete {
    println "##Initial run summary##"
    println "##Completed at: $workflow.complete"
    println "##Duration: ${workflow.duration}"
    println "##Success: ${workflow.success ? 'PASSED' : 'failed' }"
    println "##Exit status: ${workflow.exitStatus}"
    println "##Waiting for the Next Run.."
}
