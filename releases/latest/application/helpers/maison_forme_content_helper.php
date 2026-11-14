<?php defined('BASEPATH') OR exit('No direct script access allowed');

/** Store-owned copy and imagery for the Maison Forme storefront. */
function mp_maison_forme_fields(){
    $image = function($file){ return base_url('theme/images/maison_forme/'.$file); };
    $groups = [
        'Announcement & navigation' => [
            'announcement_left' => ['Announcement left text', 'INDEPENDENT IN SPIRIT.'],
            'announcement' => ['Announcement text', 'A considered wardrobe. A point of view.'],
            'announcement_right' => ['Announcement right text', 'LAGOS · EVERYWHERE'],
            'mast_left' => ['Masthead left text', 'CLOTHES FOR LIVING.\nNOT JUST THE OCCASION.'],
            'mast_right' => ['Masthead right text', 'AN INDEPENDENT\nFASHION PERSPECTIVE.'],
            'nav_story' => ['Story navigation label', 'The Journal'],
            'nav_new' => ['New arrivals menu label', 'New arrivals'],
            'nav_clothing' => ['Clothing menu label', 'Clothing'],
            'nav_bags' => ['Bags menu label', 'Bags & objects'],
        ],
        'Homepage hero' => [
            'hero_kicker' => ['Hero eyebrow', 'The everyday, reimagined / 01'],
            'hero_title' => ['Hero heading', 'A wardrobe\nof your own.', 'textarea'],
            'hero_text' => ['Hero description', 'For the way you move. The places you go. The person you are becoming.'],
            'hero_cta' => ['Hero button', 'EXPLORE THE COLLECTION'],
            'hero_photo_label' => ['Hero image caption', 'IN THE CITY. IN YOUR ELEMENT.'],
            'hero_image' => ['Hero image', $image('hero.jpg'), 'image'],
            'hero_feature_kicker' => ['Editorial feature eyebrow', 'THE STYLE NOTES'],
            'hero_feature_title' => ['Editorial feature heading', 'A little less expected.'],
            'hero_feature_cta' => ['Editorial feature button', 'READ THE EDITORIAL'],
            'editorial_image' => ['Editorial image', $image('editorial.jpg'), 'image'],
        ],
        'Homepage sections' => [
            'ticker_left' => ['Ticker left text', 'Expressive shapes. Everyday ease.'],
            'ticker_center' => ['Ticker center text', 'A new perspective on getting dressed.'],
            'ticker_right' => ['Ticker right text', 'Explore the first edit /'],
            'collection_kicker' => ['Collection eyebrow', 'THE CURRENT ROTATION'],
            'featured_heading' => ['Collection heading', 'Good pieces. Great possibilities.'],
            'collection_link' => ['Collection link label', 'VIEW ALL'],
            'collection_note' => ['Collection note', 'A FEW FAVOURITES, CHOSEN WITH INTENTION.'],
            'statement_kicker' => ['Statement eyebrow', 'OUR POINT OF VIEW'],
            'statement_text' => ['Statement text', "Personal style isn't about having more. It's about finding the pieces that feel unmistakably you."],
            'story_kicker' => ['Editorial story eyebrow', 'A STUDY IN CONTRAST'],
            'story_photo_label' => ['Editorial photo caption', 'THE EDITORIAL / STYLE NOTE 01'],
            'story_heading' => ['Story section heading', 'Find your own rhythm.'],
            'story_text' => ['Story section text', "A generous layer. A sharper line. A colour you didn't know you needed. Start with one good piece and see where the day takes you."],
            'story_cta' => ['Story button', 'INSIDE THE EDIT'],
            'story_signoff' => ['Story sign-off', 'Wear it your way.'],
            'story_image' => ['Story image', $image('editorial.jpg'), 'image'],
            'service_1_title' => ['Service 1 heading', 'A closer look'],
            'service_1_text' => ['Service 1 text', 'Explore every piece, its fit and its details.'],
            'service_2_title' => ['Service 2 heading', 'Find your fit'],
            'service_2_text' => ['Service 2 text', 'Measurements to help you choose with confidence.'],
            'service_3_title' => ['Service 3 heading', 'Make it yours'],
            'service_3_text' => ['Service 3 text', 'Save your favourites. Build your own edit.'],
            'newsletter_heading' => ['Newsletter heading', 'A little fashion. A fresh perspective.'],
            'newsletter_eyebrow' => ['Newsletter eyebrow', 'Letters from Maison Forme'],
            'newsletter_text' => ['Newsletter description', 'New edits, style notes and things we think you’ll love.'],
            'newsletter_placeholder' => ['Newsletter email placeholder', 'Your email address'],
            'newsletter_button' => ['Newsletter button', 'Subscribe'],
            'footer_copyright' => ['Footer copyright line', 'AN INDEPENDENT PERSPECTIVE.'],
            'footer_detail' => ['Footer descriptor', 'FASHION · LAGOS · EVERYWHERE'],
        ],
        'Shop & product pages' => [
            'shop_heading' => ['Shop page heading', 'The collection'],
            'shop_intro' => ['Shop page introduction', 'Pieces chosen for the way you move through the world.'],
            'shop_search' => ['Search placeholder', 'Search the collection'],
            'product_add' => ['Add to bag button', 'Add to bag'],
            'related_heading' => ['Related products heading', 'You may also like'],
            'empty_heading' => ['Empty catalogue heading', 'A new edit is on its way.'],
            'empty_text' => ['Empty catalogue text', 'Check back soon for the latest pieces.'],
        ],
    ];
    $fields=[];
    foreach($groups as $group=>$entries){
        foreach($entries as $key=>$entry){
            $fields[$key]=['group'=>$group,'label'=>$entry[0],'default'=>$entry[1],'type'=>$entry[2] ?? (mb_strlen($entry[1])>85?'textarea':'text')];
        }
    }
    return $fields;
}

function mp_maison_forme_values($json){
    $data=json_decode((string)$json,true);
    $saved=is_array($data['maison_forme'] ?? null)?$data['maison_forme']:[];
    $values=[];
    foreach(mp_maison_forme_fields() as $key=>$field){
        $values[$key]=isset($saved[$key])&&is_string($saved[$key])&&trim($saved[$key])!==''?$saved[$key]:$field['default'];
    }
    return $values;
}
