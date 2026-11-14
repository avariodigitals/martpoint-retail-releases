<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Communication_sms_templates {
    public static function catalogue(){return array(
        'custom'=>array('name'=>'Write your own','description'=>'Start with a blank message.','segment'=>'all','message'=>'Hi {name}, here is an update from {store}.'),
        'birthday'=>array('name'=>'Birthday wishes','description'=>'Customers whose recorded birthday is today. For a daily birthday check, create an Automation workflow.','segment'=>'birthday_today','message'=>'Happy birthday, {name}! Everyone at {store} wishes you a wonderful day.'),
        'welcome'=>array('name'=>'Welcome new customers','description'=>'Greet customers added in the last 30 days.','segment'=>'new_30','message'=>'Welcome, {name}! Thank you for choosing {store}. We look forward to serving you.'),
        'offer'=>array('name'=>'Special offer','description'=>'Add your offer, conditions and expiry date.','segment'=>'all','message'=>'Hi {name}, a special offer from {store}: [offer]. Valid until [date]. Terms: [conditions].'),
        'holiday'=>array('name'=>'Seasonal greeting','description'=>'Personalize for a holiday or celebration.','segment'=>'all','message'=>'Warm wishes, {name}! Everyone at {store} wishes you a wonderful [occasion].'),
        'winback'=>array('name'=>'Reconnect with customers','description'=>'Customers with no completed sale in 30 days.','segment'=>'lapsed_30','message'=>'Hi {name}, we miss you at {store}. Come back and see what is new. We would love to welcome you again.'),
        'announcement'=>array('name'=>'Business announcement','description'=>'Opening hours, a new location or a service update.','segment'=>'all','message'=>'Hi {name}, an update from {store}: [announcement]. For enquiries: [contact].'),
        'thanks'=>array('name'=>'Thank you','description'=>'Appreciate repeat customers.','segment'=>'repeat','message'=>'Thank you, {name}, for your continued support of {store}. We appreciate your business.')
    );}
}
