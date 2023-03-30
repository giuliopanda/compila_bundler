# Compila.php

Compila is a free and open source form bundler written in php.
Thanks to **compila** you can create a single html, css, js file from multiple php files (and more).
You don't have to learn any new script language, no complicated configuration, you just prepare a simple json to define the output structure.
ex:
```json
{
    "build": [
        {"files": "assets/*.css", "dest": "compila/etoile.css", "fn": "fn_build"},
        { "files": "assets/*.js", "dest": "compila/etoile.js" , "fn": "fn_build"},
        {"files": "home.html", "dest": "compila/index.html", "fn": "fn_build"}
    ], 
    "copy": [ {"files": "copy_origin/*", "dest": "assets/", "fn": "fn_copy"}]
}
```
# How to use.

Download the project and save it to your project folder by typing the terminal command

    php compila.php -copy element_name

This will copy the elements in copy_origin inside assets. It will replace {var} with the "element_name".

Instead, by writing the command from the terminal

    php compila.php -build

The files inside assets will be merged and home.html will be executed. The result will be saved inside the compila folder.

# Configure json.

In the file you can write either the name of the file to compile or use Special characters to indicate a list of files to compile.

```
\* - Matches zero or more characters.
?  - Matches exactly one character (any character).
[...] - Matches one character from a group of characters. If the first character is !, matches any character not in the group.
\ - Escapes the following character.
```
# watch

you can pass the -w or -watch parameter to monitor files and recompile them if modified

    php compila.php -build -w

# Include multiple files inside the original file.

Within the files to be compiled you can use the "e()" function that allows you to include a file inside the file you are compiling. In this way not only is any php code executed, but if you use watch, the file is monitored and the compilation rerun if modified.

# -copy param

Copies the files indicated in the json to the destination folder by replacing {var} with param
example (in the terminal): 

    php compila.php -copy folder_name

the json configuration

example (compile.json):
```json
"copy_path" : {
    "origin": "copy_origin/*", 
    "dest": "assets/"
    }
```
From command line:

    php compara.php -copy folder_name

# extend with your functions

you can create new functions such as copy or build.
To do this you have to create a php file called compila_extend.php to extend the new function
example minify

in the json you can add:
```json
"my_minify" : {
    "files": "compila/*", 
    "dest": "compila/", 
    "fn": "my_minify_function"
    }
```
From the command line you can run. Minify is not included in the default functions!!

    php compila.php -my_minify

you can see inside compila.php the copy() function for how it should be structured