#!/bin/bash

# Backup Script (meant to be run daily)

while test $# -gt 0; do
    case "$1" in
        -h|--help)
            printf "\nOptions:\n"
            printf "\n-h  --help            Print help documentation"
            printf "\n-sa --sqladdress      Set SQL IP address"
            printf "\n-sp --sqlport         Set SQL port"
            printf "\n-su --sqluser         Set SQL username"
            printf "\n-sw --sqlpassword     Set SQL password"
            printf "\n-sd --sqldatabase     Set SQL database"
            printf "\n-ma --mongoaddress    Set Mongo address"
            printf "\n-mp --mongoport       Set Mongo port"
            printf "\n-md --mongodatabase   Set Mongo database"
            printf "\n-o  --output          Set save location for backup\n"
            exit 0
            ;;
        -sa|--sqladdress)
            shift
            if test $# -gt 0; then
                    export SQLADDRESS=$1
            else
                    printf "\n! No SQL address specified\n"
                    exit 1
            fi
            shift
            ;;
        -sp|--sqlport)
            shift
            if test $# -gt 0; then
                    export SQLPORT=$1
            else
                    printf "\n! No SQL password specified\n"
                    exit 1
            fi
            shift
            ;;
        -su|--sqluser)
            shift
            if test $# -gt 0; then
                    export SQLUSER=$1
            else
                    printf "\n! No SQL user specified\n"
                    exit 1
            fi
            shift
            ;;
        -sw|--sqlpassword)
            shift
            if test $# -gt 0; then
                    export SQLPASSWORD=$1
            else
                    printf "\n! No SQL password specified\n"
                    exit 1
            fi
            shift
            ;;
        -sd|--sqldatabase)
            shift
            if test $# -gt 0; then
                    export SQLDATABASE=$1
            else
                    printf "\n! No SQL database specified\n"
                    exit 1
            fi
            shift
            ;;
        -ma|--mongoaddress)
            shift
            if test $# -gt 0; then
                    export MONGOADDRESS=$1
            else
                    printf "\n! No Mongo address specified\n"
                    exit 1
            fi
            shift
            ;;
        -mp|--mongoport)
            shift
            if test $# -gt 0; then
                    export MONGOPORT=$1
            else
                    printf "\n! No Mongo port specified\n"
                    exit 1
            fi
            shift
            ;;
        -md|--mongodatabase)
            shift
            if test $# -gt 0; then
                    export MONGODATABASE=$1
            else
                    printf "\n! No Mongo database specified\n"
                    exit 1
            fi
            shift
            ;;
        -o|--output)
            shift
            if test $# -gt 0; then
                    export OUTPUTDIRECTORY=$1
            else
                    printf "\n! No output directory specified\n"
                    exit 1
            fi
            shift
            ;;
        *)
            break
            ;;
    esac
done

if [ -z "$SQLADDRESS" ]
then
    printf "\n! Missing SQL address\n"
    printf "\n! Run -h or --help for help\n"
    exit 1;
fi

if [ -z "$SQLPORT" ]
then
    printf "\n! Missing SQL port\n"
    printf "\n! Run -h or --help for help\n"
    exit 1;
fi

if [ -z "$SQLUSER" ]
then
    printf "\n! Missing SQL user\n"
    printf "\n! Run -h or --help for help\n"
    exit 1;
fi

if [ -z "$SQLPASSWORD" ]
then
    printf "\n! Missing SQL password\n"
    printf "\n! Run -h or --help for help\n"
    exit 1;
fi

if [ -z "$SQLDATABASE" ]
then
    printf "\n! Missing SQL database\n"
    printf "\n! Run -h or --help for help\n"
    exit 1;
fi

if [ -z "$MONGOADDRESS" ]
then
    printf "\n! Missing Mongo address\n"
    printf "\n! Run -h or --help for help\n"
    exit 1;
fi

if [ -z "$MONGOPORT" ]
then
    printf "\n! Missing Mongo port\n"
    printf "\n! Run -h or --help for help\n"
    exit 1;
fi

if [ -z "$MONGODATABASE" ]
then
    printf "\n! Missing Mongo database\n"
    printf "\n! Run -h or --help for help\n"
    exit 1;
fi

if [ -z "$OUTPUTDIRECTORY" ]
then
    printf "\n! Missing output directory\n"
    printf "\n! Run -h or --help for help\n"
    exit 1;
fi

ARCHIVESIZE="8" # Number of full backups to keep (plus one)

SQLDUMP="sql_dump.sql"
MONGODUMP="mongo_dump"
TARDIR="`date +'%m-%d-%Y-at-%H-%M-%S'`"

mkdir $TARDIR # Create the folder we'll eventually tar

mysqldump --opt -h $SQLADDRESS -P $SQLPORT -u $SQLUSER -p "$SQLPASSWORD" --routines $SQLDATABASE > $SQLDUMP # Dump SQL database to file
mongodump -h $MONGOADDRESS:$MONGOPORT -d $MONGODATABASE -o $MONGODUMP --quiet # Dump Mongo and save to file

# Move the dumps to the directory that we'll tar
mv $SQLDUMP $TARDIR
mv $MONGODUMP $TARDIR

tar -zcvf "${TARDIR}.tar.gz" $TARDIR # Create a compressed tar of dumps

if [ ! -d $OUTPUTDIRECTORY ] # Create archival backups folder if it doesn't exist
then
    mkdir -p $OUTPUTDIRECTORY
fi

mv "${TARDIR}.tar.gz" $OUTPUTDIRECTORY
rm -rf $TARDIR

if [ -f "${OUTPUTDIRECTORY}/${TARDIR}.tar.gz" ] # Check that the tar file exists in the proper place
then
    printf "\n! Backup created successfully\n"
else
    printf "\n! There was an error creating the backup\n"
fi

cd $OUTPUTDIRECTORY && ls -t *.tar.gz | tail -n +$ARCHIVESIZE | xargs rm # Remove the oldest archived backup if we have more than the number defined in ARCHIVESIZE
