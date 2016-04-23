# REST API with MySQLi - CRUD Operations
Rest API class with MySQLi based CRUD operations and User Module as demo.

### Developed By
Bharat Parmar

### Version
1.1

## File Structure
  * config.php  : Configuration File 

  * bharatcode.sql : Database File.

  * class/Main.class.php : Main class file which contains many useful methods for database operations, mail sending, validation.

  * rest/.htaccess : HTACCESS file for the URL redirection

  * rest/Rest.inc.php : This class file contains REST Standard basis api related methods.

## Requirements 
PHP Version : 3.0 or above

## Sample Code

### Get Users

  Request: 

        GET /bharat/restful/rest/users HTTP/1.1
        Host: localhost
        Cache-Control: no-cache
        Postman-Token: 94ce58e8-5db7-4df4-19e5-457b29586d5f

### Register User

  Request: 

        POST /bharat/restful/rest/register HTTP/1.1
        Host: localhost
        Cache-Control: no-cache
        Postman-Token: ec8d2516-818d-4f3d-a417-9903575ccf81
        Content-Type: application/x-www-form-urlencoded
        
        Parameters : firstname, lastname, email, password
        firstname=Jack&email=jackthomas@gmail.com&lastname=Thomas&password=123456

  Response: 

        {
          "status": "success",
          "message": "register successfully.",
          "data": {
            "user_id": 11
          }
        }

### Delete User

  Request: 

        DELETE /bharat/restful/rest/deleteuser?id=11 HTTP/1.1
        Host: localhost
        Cache-Control: no-cache
        Postman-Token: 79e1e8cb-60a8-993a-7e63-d2831ed9ac16
        Content-Type: multipart/form-data;

  Response: 

        {
          "status": "success",
          "message": "Total 1 record(s) Deleted.",
          "data": 1
        }

### Login

  Request: 

        POST /bharat/restful/rest/login HTTP/1.1
        Host: localhost
        Cache-Control: no-cache
        Postman-Token: 651c7ef3-da80-0624-f519-b4ca8d39e8f5
        Content-Type: application/x-www-form-urlencoded
        
        Parameters : email, password
        email=jackthomasgmail.com&password=123456

  Response:

        {
          "status": "success",
          "message": "logged in successfully.",
          "data": {
            "user_id": 11
          }
        }
