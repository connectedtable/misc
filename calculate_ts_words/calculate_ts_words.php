#!/usr/bin/env php
<?php
    #
    # Copyright (C) 2012, Connected Table AB
    #
    # PHP script for calculating the number of translatable words and strings in a
    # Qt Linguist .ts-file.
    #
    # Usage:
    # ./calculate_ts_words -f <file>
    # -f can be specified more than once
    #

    require_once 'Console/Getopt.php';

    // Function copied from
    // http://pear.php.net/manual/en/package.console.console-getopt.examples.php#14798
    function &condense_arguments($params)
    {
        $new_params = array();

        foreach ($params[0] as $param) {
            if (isset($new_params[$param[0]])) {
                if (!is_array($new_params[$param[0]])) {
                    $new_params[$param[0]] = (array)$new_params[$param[0]];
                }
                $new_params[$param[0]][] = $param[1];
            } else {
                $new_params[$param[0]] = $param[1];
            }
        }

        return $new_params;
    }

    function print_help()
    {
        echo "./calculate_ts_words.php -f <file> [-f <file2>]\n";
    }

    $cg = new Console_Getopt();
    $shortoptions = 'f:h';
    $arguments    = $cg->readPHPArgv(); 
    array_shift($arguments);

    // Parse arguments
    $params = $cg->getopt2($arguments, $shortoptions);
    if (PEAR::isError($params)) {
        echo 'Error: '.$params->getMessage()."\n";
        print_help();
        exit(1);
    }

    // Condense arguments, look for help keywords and format file argument into
    // an arrat.
    $params = condense_arguments($params);
    if (array_key_exists('h', $params) || 
        !array_key_exists('f', $params)) {
        print_help();
        exit;
    } 

    if (!is_array($params['f'])) {
        $params['f'] = array($params['f']);
    }

    $strings = 0;
    $words = 0;

    // Load and parse the files
    foreach($params['f'] as $file) {
        // Check for file existens
        if (!file_exists($file)) {
            echo "File $file doesn't exist!\n";
            continue;
        }
        // Load file
        $xmlfile = @simplexml_load_string(file_get_contents($file));
        if (!$xmlfile) {
            echo "Could not load and/or parse file $file\n";
            continue;
        }

        // Locate all source strings
        $source_strings = $xmlfile->xpath('context/message/source');
        
        // Count source strings
        $nrofstrings = count($source_strings);
        echo $file.' strings: '.$nrofstrings."\n";
        $strings += $nrofstrings;

        // Count words
        $nrofwords = 0;
        foreach($source_strings as $string) {
            $nrofwords += str_word_count((string)$string);
        }
        echo $file.' words: '.$nrofwords."\n";
        $words += $nrofwords;
    }

    // Print total
    echo "\n";
    echo "Total strings: $strings\n";
    echo "Total words: $words\n";

