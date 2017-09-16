<?php

$language_array = array("nl","en");

if(in_array(GLOBALDEF_LANGUAGE, $language_array)){

    #Load language
    include('general/language-general-'.GLOBALDEF_LANGUAGE.'.php');
}