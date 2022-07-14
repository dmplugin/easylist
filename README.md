# PHP EasyList - Version 1.0
#### by 
## PHP Team DigitalMesh



### Introduction

EasyList is a PHP plugin which lets the developer to implement listing,pagination and filtering with less code and effort. With the help of composer the plugin can be included to the projects which are developed using any frameworks in PHP which makes EasyList versatile.

### Requirements
- PHP version supported from 5.6 to 7.4(Testing is in progress for PHP 8)
- JQuery and Bootstrap

### Supporting Databases
EasyList supports the following databases
- MySQL
- POSTGRESQL
- MSSQL
- ORACLE

### Features
- Page control. This will prepare query and apply filter based on the paramters provided and returns data as Json/Object. This control also provides a feature to get the SQL query for debugging
- Pager control is to navigate to different pages 
- Listing control. This will support both Postback and Ajax rendering

### Installation
EasyList is supported in PHP versions 5.6 to 7.4.The plugin requires Bootstrap and JQuery preinstalled.

##### Using Composer
If the respective project includes composer EasyList can be added to the project by the following command.
```sh 
composer require easylist/easylist2
```
##### Git
EasyList can be downloaded or cloned from 
```sh
https://github.com/pknairr/easylist2.git
```
##### Configuring 
If the plugin was included to the project through composer. The file 
```sh
vendor/easylist2/config/EasyListConfig.php
```
needs to be configured.

Copy this file to any desired location in the project 
The line number 19 of this file contains the following code
```sh
19 $configPath = 'app/config/EasyListConfig.php';
```
Change the path mentioned there to the path of the configuration file of the project. For Laravel projects this would be the path of the .env file.

### Usage
There are three function for listing,pagination and rendering filtered result to the DOM.
- Page
- List
- Pager

Page function have to be added in the controller. List and Pager function should be added to the view.

Add the file EasyList.php
```sh
require 'vendor/easylist2/EasyList.php'
```
to the  desired code block.

### Page Function
This function is called from the controller part.

```sh
Listing::Page(array());
```
There are some parameters that need to be given in the array. The params are mentioned below
| Param | Description |
| ------ | ------ |
| select |  Comma separated column list along with alias |
| from   |  From table with alias       |
| joins  |  Join statements             |
| conditions |  Array which consists of the conditions that has to be applied in the ***<WHERE>*** clause. Its to mention extra conditions other than filters |
| group | Comma separated group names  |
| having| Array which consists of the conditions and values for ***<HAVING>*** clause|
| having_columns| Comma separated list of columns used in the ***HAVING*** clause.|
|filters|Array which consists of conditions for the filters from the front end|
|order|Comma separated order columns with **ASC/DESC** key |
|return_data|Format of the data that has to be returned. Options are : ***HTML / JSON / OBJECT / QUERY***|
|view|view location if return_data is HTML. This is for rendering data through ajax when developer wants custom tables|
|view_variables|Array variables that has to be passed to the view|
|page|page number|
|pagination|Used to mention whether pagination is present or not. Options : ***YES / NO***|
|page_size|Number of records that has to be shown in each page|

These are the options for filter
 
| Option | Description |Possible values
| ------ | ------ |------|
|condition|The condition which has to be included in the ***WHERE*** clause|-|
|form-field|The field from which the value is taken for the particular condition|-|
|operation|This option specifies which operation binds the condition to other consitions|***AND/OR***|
|type|The type of the form-field|***BOOLEAN/DATE/DATETIME/TIME/INTEGER/STRING/ARRAY**|
|datetime_format_from|Date time format of th field|-|
|datetime_format_to|The date time format to which the field value has to be converted|-|
|consider_empty|If the option consider_empty is YES then the field's condition will be considered for the query created automatically otherwise it will be ruled out. By default this option's value is NO.|***YES/NO***|
|type|Option : COMPLEX. This is to include complex query. In this case normal condition array will be added to an array this option. See the example section|-|

``If both conditions and filters options are present filters will have more preference. ``

These are the options for condition
 
| Option | Description |Possible values
| ------ | ------ |------|
|condition|The condition which has to be included in the ***WHERE*** clause|-|
|value|The values which has to be added to the respective ***WHERE*** clause|-|
|operation|This option specifies which operation binds the condition to other consitions|***AND/OR***| 

 
###### Examples
 **select**
 ```sh
 Listing::Page(array(
        "select" => "id,name" 
 ));
 ```
 
 **from**
 ```sh
  Listing::Page(array(
        "select" => "id,name",
        "from"   => "user AS usr"
 ));
 ```
 
 **joins**
 ```sh
  Listing::Page(array(
        "select" => "id,name",
        "from"   => "user AS usr",
        "joins"  => " INNER JOIN login AS lgn  ON usr.id = lgn.user_id"
 ));
 ```
 **conditions**
  ```sh
  Listing::Page(array(
        "select"     => "id,name",
        "from"       => "user AS usr",
        "conditions" => array(
            array("condition" => "id > ?","value" => 10, "operation" => "AND"),
            array("condition" => "code <> ?","value" => "AU", "operation" => "AND")
        )
 ));
 ```
 
 **group**
  ```sh
  Listing::Page(array(
        "select" => "id,name",
        "from"   => "user AS usr",
        "group"  => "code"
 ));
 ```
 
 **having**
  ```sh
  Listing::Page(array(
        "select" => "id,name",
        "from"   => "customer AS cust",
        "group"  => "code",
        "having" => array(
            array("condition" => "count(*) > ?", "value" => 5, "operation" => "OR")
        )
 ));
 ```
 
 **having_columns**
   ```sh
  Listing::Page(array(
        "select" => "id,name",
        "from"   => "customer AS cust",
        "group"  => "code",
        "having" => array(
            array("condition" => "age = ?", "value" => 25, "operation" => "AND")
            array("condition" => "count(*) < ?", "value" => 10, "operation" => "AND")
        ),
        "having_columns"        => "age" 
 ));
 ```
 
 **filters**
 ```sh
  Listing::Page(array(
        "select"  => "id,name",
        "from"    => "customer AS cust",
        "filters" => array(
            array("condition" => "cust.name = ?", "form-field" => "txt_name", 
                 "operation" => "AND", "type"=>"STRING",
                 "consider_empty" => "YES"),
            array("condition" => "cust.created_date = ?", "form-field" => "c_date", 
            "operation" => "OR", "type"=>"DATE", 
            "datetime_format_from"=>"d/m/Y", "datetime_format_to"=>"Y-m-d", 
            "consider_empty" => "NO")
        )
 ));
 ```
 **complex filters**
 ```sh
  Listing::Page(array(
        "select"  => "id,name",
        "from"    => "customer AS cust",
        "filters" => array(
            array("condition" => "cust.name = ?", "form-field" => "txt_name", 
                 "operation" => "AND", "type"=>"STRING",
                 "consider_empty" => "YES"),
            array("condition" => "cust.created_date = ?", "form-field" => "c_date", 
            "operation" => "OR", "type"=>"DATE", 
            "datetime_format_from"=>"d/m/Y", "datetime_format_to"=>"Y-m-d", 
            "consider_empty" => "NO"),
            array("type"=>"COMPLEX", "operation" => "AND", "condition" =>array(
               array("condition" => "(cust.name = ?", 
               "form-field" => "txt_name", "operation" => "AND", "type"=>"STRING",
                   "consider_empty" => "YES"),
               array("condition" => "cust.area = ?)", "form-field" => "txt_name",
               "operation" => "AND", "type"=>"STRING",
                   "consider_empty" => "YES"),
            )
        )
 ));
 ```             

**order**
```sh
Listing::Page(array(
    "select" => "id,name",
    "from"   => "customer AS cust",
    "order" => "name ASC"
));
```

**return_data**
```sh
Listing::Page(array(
    "select" => "id,name",
    "from"   => "customer AS cust",
    "return_data" => "JSON"
));
```
``return_data can have  HTML / JSON / OBJECT / QUERY``

**view**
```sh
Listing::Page(array(
    "select"      => "id,name",
    "from"        => "customer AS cust",
    "return_data" => "HTML"
    "view"        => "views/list.php"
));
```
**view_variables**
```sh
Listing::Page(array(
    "select"         => "id,name",
    "from"           => "customer AS cust",
    "return_data"    => "HTML",
    "view"           => "views/list.php",
    "view_variables" => array("ids"=>$ids,"names"=>$names)
));
```

**page**
```sh
Listing::Page(array(
    "select"         => "id,name",
    "from"           => "customer AS cust",
    "return_data"    => "HTML",
    "view"           => "views/list.php",
    "view_variables" => array("ids"=>$ids,"names"=>$names),
    "page"           => 1
));
```
**pagination**
```sh
Listing::Page(array(
    "select"         => "id,name",
    "from"           => "customer AS cust",
    "return_data"    => "JSON",
    "page"           => 1,
    "pagination"     => "YES"
));
```
``Default value for pagination is YES``

**page_size**
```sh
Listing::Page(array(
    "select"         => "id,name",
    "from"           => "customer AS cust",
    "return_data"    => "JSON",
    "page"           => 1,
    "pagination"     => "YES"
    "page_size"      => 25
));
```

### List Function
List function is used in the view page. This function directs the control to the URL from where the data should be formatted and shown. It places the resulting list to the desired area of the DOM.

```sh
Listing::List(array());
```

There parameters for list function is mentioned below
| Param | Description |
| ------ | ------ |
|url|Target location of controller/action function|
|form_id|Id of the filter form|
|target_div_id|Div id where we want to show the output|
|button_id|ID of the filtering button|
|autolist|Can have true / false : If true will show the output first time without pressing the button|
|column|Specify the columns of the list|
|data|Provides data returned by the page function here. This is for post back rendering|
|return_data|This is to specify the data type of page function. Options are : ***HTML / JSON / OBJECT***|             
|pager|Location where we want to show the page controller. Options : ***TOP/BOTTOM/BOTH***|
|page_size|ProvideS the array option for pagination. The Default is ***array(10,25,50,100,250)***|             
|action|To point out the action columns where edit,delete and show links should be shown. Here developer can use ***{<Table_column_name>}*** to add values in the script/html|

Options of column 

| Option | Description |
| ------ | ------ |
|head|Header for the particular column|
|column|The param from the JSON response fom which the data has to be taken|
|width|Width of the column|
|sort|Which column should be considered for sorting|
|class|Mention the CSS class|             
             
             
###### Examples
**url,form_id,target_div_id,button_id,column,pager**
These 6 params are mandatory fields for List() function.
```sh
Listing::List(array(
    "url"           => "customer/list",
    "form_id"       => "user_filter_form",
    "target_div_id" => "user_list_div",
    "button_id"     => "user_list",
    "column"        => array(
                            array("head" => "Code", "column" => "a_code",
                            "width" => '30%',"sort" => "a_code",
                            "class" => "text-center"),
                            array("head" => "Town", "column" => "a_town", 
                            "width" => '30%', "class" => "",
                            "sort" => "a_town"),
                            array("head" => "Address", "column" => "a_state", 
                            "width" => '40%', "class" => "",
                            "sort" => "address")
                       ),
    "pager"          => "BOTH"
));
```
######  Column params
####


**autolist**
```sh
Listing::List(array(
    "url"           => "customer/list",
    "form_id"       => "user_filter_form",
    "target_div_id" => "user_list_div",
    "button_id"     => "user_list",
    "autolist"       => true,
    "column"        => array(
                            array("head" => "Code", "column" => "a_code",
                            "width" => '30%',"sort" => "a_code",
                            "class" => "text-center"),
                            array("head" => "Town", "column" => "a_town", 
                            "width" => '30%', "class" => "",
                            "sort" => "a_town"),
                            array("head" => "Address", "column" => "a_state", 
                            "width" => '40%', "class" => "",
                            "sort" => "address")
                       ),
    "pager"          => "BOTH"
));
```
``'autolist' can either have  true/false``

**action**
```sh
Listing::List(array(
    "url"           => "customer/list",
    "form_id"       => "user_filter_form",
    "target_div_id" => "user_list_div",
    "button_id"     => "user_list",
    "column"        => array(
                            array("head" => "Code", "column" => "a_code",
                            "width" => '30%',"sort" => "a_code",
                            "class" => "text-center"),
                            array("head" => "Town", "column" => "a_town", 
                            "width" => '30%', "class" => "",
                            "sort" => "a_town"),
                            array("head" => "Address", "column" => "a_state", 
                            "width" => '40%', "class" => "",
                            "sort" => "address")
                       ),
    "pager"          => "BOTH",
    "return_data"    => "HTML",
    "action"         => array("<a 
                                href='http://www.test.com/{name}/index'>
                                <span class='glyphicon glyphicon-pencil'>
                                </span></a>&nbsp;",
                                "&nbsp;<a href='http://www.test.com/{id}/delete'>
                                <i style='color:red;font-size:20px;'
                                class='fa fa-trash-o'></i></a>&nbsp;",
                                '<a 
                                href="http://www.test.com/{xx}/view">
                                <span style="font-size:20px;" class="fa fa-eye">
                                </span></a>'
                           )
));
```
### Pager Function 
Pager function is to show pagination in the listing page.

```sh
Listing::Pager();
```
| Param | Description |
| ------ | ------ |
|page|This is a mandatory parameter.The page object is passed ie the return value from the Page function.|
|form_id|The form id should be given in this param|
|page_sizes|The array of values for the different page sizes for listing.|

##### Examples
**pager**
```sh
Listing::Pager(array(
                "page" => $result, 
                "form_id" => "postbackform", 
                "page_size" => array(10,25,50,100,250)));
