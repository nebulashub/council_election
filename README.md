# Description

This project is a web service for nebulas election, which provide APIs for frontend

The project is writen by PHP (v7.2).
 
Dependce on the following PHP extensions:

    * gmp
    * swoole (v4.3.3)
    * phalcon (v3.4.2)


# Install

There are 3 files or folders in the root path.

api: project code

php7: files for building php7 docker image

Dockerfile: file for building project docker image

* Step 1: git colne

        cd [root_path]

        git submodule update --init

* Step 2: build PHP7 docker image

        cd [root_path]/php7
    
        docker build -t php7:nebulas .

* Step 3: build project docker image
    
        cd [root_path]
    
        docker build -t nebulas:[version] .

* Step 4: run container by project image

        docker run --name nebulas -p [your port]:8080 nebulas -d nebulas:[version] crontab env=[enviroment]
    
    * [enviroment] can be "production", "ready" or "dev", default "production", that means the project uses which config file ([root_path]/api/config)
    
    * you can edit any file in path [root_path]/api/config and make adjustments as needed
