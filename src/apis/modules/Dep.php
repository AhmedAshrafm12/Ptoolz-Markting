<?php 
namespace modules;
class Dep{
 
    function call_api():float
    {

       sleep(4);
       return 7.1;


    }

    function success()
    {
        return true;
    }
    function send_email( array $customer, $view) : bool
    {

        sleep(2);

         $name = $customer['name'];
         $messg = $customer['msg'];
         $view = $view;

         return true;
    }
}