<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Email designs a store can own. Content is stored as validated blocks and
 * escaped when the email is built, so neither the MartPoint hub nor the editor
 * can ever deliver scripts, broken markup or forged links.
 */
class Communication_designs {
    const MAX_BLOCKS = 12;
    const DEFAULT_ACCENT = '#6554c0';
    const FORMATS = array('html'=>'Designed HTML','plain'=>'Plain letter');
    const CATEGORIES = array('blank'=>'Blank','announcement'=>'Announcements','offer'=>'Offers and promotions','newsletter'=>'Newsletters','welcome'=>'Welcomes','event'=>'Events','reminder'=>'Reminders and thanks','winback'=>'Win-back');
    const INDUSTRIES = array('any'=>'Every business','retail'=>'Shops and retail','food'=>'Food and drink','health'=>'Clinics and pharmacies','beauty'=>'Salon and beauty','fashion'=>'Fashion and apparel','tech'=>'Electronics and gadgets','home'=>'Home and building','learning'=>'Schools and books','print'=>'Print and stationery','services'=>'Local services');
    const OCCASIONS = array('any'=>'Any time','launch'=>'New opening','restock'=>'New stock','seasonal'=>'Season and festive','birthday'=>'Birthdays','appointment'=>'Appointments','feedback'=>'Reviews and referrals','winback'=>'We miss you');
    const BRANDS = array('name'=>'Store name','initials'=>'Store initials','none'=>'No brand mark');
    const BLOCKS = array(
        'heading'=>array('label'=>'Heading','field'=>'text'),
        'subtitle'=>array('label'=>'Short lead','field'=>'text'),
        'body'=>array('label'=>'Your message','field'=>'none'),
        'paragraph'=>array('label'=>'Paragraph','field'=>'text'),
        'bullets'=>array('label'=>'Bullet list','field'=>'text'),
        'callout'=>array('label'=>'Highlight box','field'=>'text'),
        'button'=>array('label'=>'Button','field'=>'both'),
        'divider'=>array('label'=>'Divider','field'=>'none'),
        'terms'=>array('label'=>'Fine print','field'=>'text'));

    /**
     * Curated MartPoint designs. Central edits or adds to these in its own
     * console; stores adopt a copy and make it theirs.
     */
    public static function catalogue() {
        $set=function($key,$name,$format,$category,$industry,$occasion,$accent,$subject,$preheader,$blocks){
            return array('slug'=>$key,'name'=>$name,'format'=>$format,'category'=>$category,'industry'=>$industry,
                'occasion'=>$occasion,'accent'=>$accent,'subject'=>$subject,'preheader'=>$preheader,'blocks'=>$blocks);
        };
        return array(
            'blank'=>$set('blank','Blank canvas','html','blank','any','any',self::DEFAULT_ACCENT,'A message from {store}','Something worth your two minutes',array(
                array('type'=>'body'))),
            'letter'=>$set('letter','Personal letter','plain','announcement','any','any',self::DEFAULT_ACCENT,'A message from our team','A short note from the people behind the counter',array(
                array('type'=>'paragraph','text'=>'Thank you for being with {store}.'))),
            'welcome'=>$set('welcome','Warm welcome','html','welcome','any','any','#2f6fed','Welcome — we are glad you are here','Three quick ways to get started with us',array(
                array('type'=>'heading','text'=>'Welcome to {store}'),
                array('type'=>'subtitle','text'=>'Here is what new customers usually do first.'),
                array('type'=>'body'),
                array('type'=>'bullets','text'=>'Browse what is in stock right now\nReserve an item for pickup\nReply to this email and talk to a real person'),
                array('type'=>'button','label'=>'Start browsing','url'=>''),
                array('type'=>'terms','text'=>'You are receiving this because you created an account with us.'))),
            'offer'=>$set('offer','Offer spotlight','html','offer','any','restock','#d1477a','Something special for you','Ends soon — here is the whole offer',array(
                array('type'=>'heading','text'=>'This week at {store}'),
                array('type'=>'callout','text'=>'Add the offer, the dates and the conditions here.'),
                array('type'=>'body'),
                array('type'=>'button','label'=>'Claim the offer','url'=>''),
                array('type'=>'terms','text'=>'Offer applies while stock lasts. Terms shown in the message above.'))),
            'newsletter'=>$set('newsletter','Monthly newsletter','html','newsletter','any','any','#0f7f8c','Your latest news and updates','What is new, what is coming, what you asked for',array(
                array('type'=>'heading','text'=>'This month at {store}'),
                array('type'=>'paragraph','text'=>'Here is what is new this month.'),
                array('type'=>'body'),
                array('type'=>'divider'),
                array('type'=>'bullets','text'=>'Share your news\nAdd a useful tip\nMention an upcoming event'),
                array('type'=>'button','label'=>'Read the full update','url'=>''))),
            'announcement'=>$set('announcement','Clear announcement','html','announcement','any','any','#475569','An important update','What changed and what it means for you',array(
                array('type'=>'subtitle','text'=>'An important update from {store}.'),
                array('type'=>'body'),
                array('type'=>'callout','text'=>'Need help? Reply to this email and we will answer.'))),
            'launch'=>$set('launch','New stock and launches','html','offer','retail','launch','#c2410c','Just landed at {store}','Fresh on the shelf — while stock lasts',array(
                array('type'=>'heading','text'=>'Just landed'),
                array('type'=>'body'),
                array('type'=>'bullets','text'=>'What is new\nWhy it is worth a look\nWhat it costs'),
                array('type'=>'button','label'=>'See the arrival','url'=>''))),
            'menu_feature'=>$set('menu_feature','Menu feature','html','offer','food','restock','#b45309','New on the menu','Made this week, ready today',array(
                array('type'=>'heading','text'=>'On the menu this week'),
                array('type'=>'callout','text'=>'Tell customers what makes this dish worth ordering.'),
                array('type'=>'body'),
                array('type'=>'button','label'=>'Order for pickup','url'=>''))),
            'appointment_reminder'=>$set('appointment_reminder','Appointment reminder','html','reminder','health','appointment','#0e7490','Your visit with us','Time, place and how to reschedule',array(
                array('type'=>'heading','text'=>'Your visit with {store}'),
                array('type'=>'body'),
                array('type'=>'terms','text'=>'Please arrive ten minutes early. Reply to this email to reschedule.'))),
            'rebooking'=>$set('rebooking','Time to book again','html','winback','beauty','appointment','#a21caf','It has been a while — book your next visit','Pick a time that suits you',array(
                array('type'=>'subtitle','text'=>'It has been a while since we saw you.'),
                array('type'=>'body'),
                array('type'=>'button','label'=>'Book a time','url'=>''),
                array('type'=>'terms','text'=>'You are receiving this because you have visited us before.'))),
            'event_invite'=>$set('event_invite','Event invitation','html','event','any','seasonal','#6d28d9','You are invited','Date, time and how to reserve a place',array(
                array('type'=>'heading','text'=>'You are invited'),
                array('type'=>'paragraph','text'=>'Add the date, the time and the place.'),
                array('type'=>'body'),
                array('type'=>'button','label'=>'Reserve a place','url'=>''))),
            'thank_you'=>$set('thank_you','Thank you note','html','reminder','any','any','#15803d','Thank you for your order','What happens next',array(
                array('type'=>'heading','text'=>'Thank you, {name}'),
                array('type'=>'body'),
                array('type'=>'bullets','text'=>'When to expect it\nHow to reach us\nWhat to do if something is wrong'))),
            'feedback_ask'=>$set('feedback_ask','Tell us how we did','html','reminder','any','feedback','#1d4ed8','How did we do?','Two minutes that helps the next customer',array(
                array('type'=>'subtitle','text'=>'We would love to hear how your last visit went.'),
                array('type'=>'body'),
                array('type'=>'button','label'=>'Leave a review','url'=>''))),
            'seasonal'=>$set('seasonal','Season and festive','html','offer','any','seasonal','#b91c1c','Our season line-up','Gifts, bundles and festive hours',array(
                array('type'=>'heading','text'=>'The season is here'),
                array('type'=>'callout','text'=>'Add your festive offer, bundles or opening hours.'),
                array('type'=>'body'),
                array('type'=>'button','label'=>'See the line-up','url'=>''))),
            'birthday'=>$set('birthday','Birthday greeting','html','welcome','any','birthday','#be185d','Happy birthday, {name}','A little something from us to you',array(
                array('type'=>'heading','text'=>'Happy birthday, {name}'),
                array('type'=>'body'),
                array('type'=>'button','label'=>'Use my gift','url'=>''))),
            'service_special'=>$set('service_special','Service special','html','offer','services','any','#0f766e','A special on your next visit','Book it while the offer runs',array(
                array('type'=>'heading','text'=>'A special for you'),
                array('type'=>'body'),
                array('type'=>'bullets','text'=>'What is included\nHow long it takes\nWhat it costs'),
                array('type'=>'button','label'=>'Book the service','url'=>''))),
            'boutique_drop'=>$set('boutique_drop','New season edit','html','offer','fashion','restock','#c026d3','New in at {store}','Fresh pieces, while the sizes last',array(
                array('type'=>'heading','text'=>'New season, new stock'),
                array('type'=>'body'),
                array('type'=>'bullets','text'=>'What just arrived\nWhat is reduced\nWhat is nearly gone'),
                array('type'=>'button','label'=>'See the new pieces','url'=>''))),
            'gadget_spotlight'=>$set('gadget_spotlight','Gadget spotlight','html','offer','tech','launch','#1e40af','Just landed at {store}','What is new, what it does, what it costs',array(
                array('type'=>'heading','text'=>'Fresh off the shelf'),
                array('type'=>'callout','text'=>'Name the model and the one thing it does better than the last one.'),
                array('type'=>'body'),
                array('type'=>'button','label'=>'Compare the models','url'=>''))),
            'home_project'=>$set('home_project','Project planner','html','announcement','home','any','#92400e','Planning a project? Start here','What to buy, in what order',array(
                array('type'=>'subtitle','text'=>'A short guide from {store}.'),
                array('type'=>'body'),
                array('type'=>'bullets','text'=>'Measure first\nWhat to buy together\nWhat we can deliver'),
                array('type'=>'button','label'=>'Ask us for a quote','url'=>''))),
            'school_term'=>$set('school_term','Back to school','html','announcement','learning','seasonal','#4338ca','Ready for the new term','Uniforms, books and supplies in one place',array(
                array('type'=>'heading','text'=>'Ready for the new term'),
                array('type'=>'body'),
                array('type'=>'bullets','text'=>'The list we stock\nWhat is in size now\nHow to reserve and collect'),
                array('type'=>'button','label'=>'Reserve the list','url'=>''))),
            'print_order'=>$set('print_order','Print and branding','html','announcement','print','any','#7c2d12','What we can print for you','Cards, seals, banners and what they cost',array(
                array('type'=>'heading','text'=>'Print it, and we will finish it'),
                array('type'=>'body'),
                array('type'=>'bullets','text'=>'Name the item and quantity\nChoose the finish\nTell us the date you need it'),
                array('type'=>'button','label'=>'Send your job file','url'=>''))),
            'winback'=>$set('winback','We miss you','html','winback','any','winback','#7c3aed','We miss you at {store}','Come back — here is what is new',array(
                array('type'=>'subtitle','text'=>'It has been a while, and a few things have changed.'),
                array('type'=>'body'),
                array('type'=>'button','label'=>'See what is new','url'=>''))));
    }

    /** Any catalogue slug, in the same shape a stored row arrives in. */
    public static function find($slug) {
        $rows=self::catalogue();
        return isset($rows[$slug])?self::normalize($rows[$slug]):null;
    }

    /** Hub rows and catalogue entries both arrive here. */
    public static function normalize(array $row) {
        $flag=isset($row['show_unsubscribe'])?$row['show_unsubscribe']:($row['unsubscribe']??1);
        $pick=function($key,array $map,$fallback)use($row){$v=(string)($row[$key]??'');return isset($map[$v])?$v:$fallback;};
        return array(
            'id'=>(int)($row['id']??0),
            'key'=>(string)($row['base_template_key']??($row['slug']??'')),
            'name'=>mb_substr(trim((string)($row['name']??''))?:'Email design',0,120),
            'format'=>$pick('format',self::FORMATS,'html'),
            'category'=>$pick('category',self::CATEGORIES,'announcement'),
            'industry'=>$pick('industry',self::INDUSTRIES,'any'),
            'occasion'=>$pick('occasion',self::OCCASIONS,'any'),
            'subject'=>mb_substr((string)($row['subject']??''),0,255),
            'preheader'=>mb_substr((string)($row['preheader']??''),0,180),
            'accent'=>self::accent($row['accent']??''),
            'brand'=>$pick('brand_display',self::BRANDS,'name'),
            'unsubscribe'=>!empty($flag)?1:0,
            'cta_label'=>mb_substr(trim((string)($row['cta_label']??'')),0,80),
            'cta_url'=>self::url($row['cta_url']??''),
            'blocks'=>self::blocks($row['blocks_json']??($row['blocks']??null)));
    }

    /** Blocks are data: unknown types, oversized text and non-HTTPS links are dropped. */
    public static function blocks($value) {
        if (is_string($value)) $value = json_decode($value,true);
        $out=array();
        foreach ((array)$value as $block) {
            if (count($out)>=self::MAX_BLOCKS) break;
            if (!is_array($block)) continue;
            $type=(string)($block['type']??'');
            if (!isset(self::BLOCKS[$type])) continue;
            $item=array('type'=>$type,'text'=>mb_substr(trim((string)($block['text']??'')),0,2000));
            if (self::BLOCKS[$type]['field']==='both') {
                $item['label']=mb_substr(trim((string)($block['label']??'')),0,80);
                $item['url']=self::url($block['url']??'');
            }
            $out[]=$item;
        }
        return $out ?: array(array('type'=>'body','text'=>''));
    }

    /** POST → stored row. Anything unusable is refused rather than quietly rewritten. */
    public static function sanitize(array $p) {
        $name=trim((string)($p['name']??''));
        if ($name==='' || mb_strlen($name)>120) throw new RuntimeException('Name the design, up to 120 characters.');
        $format=(string)($p['format']??'html');
        if (!isset(self::FORMATS[$format])) throw new RuntimeException('Choose Plain letter or Designed HTML.');
        $category=(string)($p['category']??'announcement');
        if (!isset(self::CATEGORIES[$category])) throw new RuntimeException('Choose a category for this design.');
        $blocks=self::blocks($p['blocks']??null);
        $used=array();foreach($blocks as $block)$used[$block['type']]=1;
        if (!isset($used['body'])) array_unshift($blocks,array('type'=>'body','text'=>''));
        $subject=trim((string)($p['subject']??''));
        if (mb_strlen($subject)>255) throw new RuntimeException('Keep the example subject under 255 characters.');
        $url=self::url($p['cta_url']??'');
        if (trim((string)($p['cta_url']??''))!=='' && $url==='') throw new RuntimeException('The button link must start with https://');
        $brand=(string)($p['brand_display']??'name');
        if (!isset(self::BRANDS[$brand])) $brand='name';
        $accent=self::accent($p['accent']??'');
        if ((string)($p['accent']??'')!=='' && !preg_match('/^#[a-f0-9]{6}$/iD',(string)$p['accent'])) throw new RuntimeException('Use a colour code such as #6554c0.');
        $industry=(string)($p['industry']??'any');if(!isset(self::INDUSTRIES[$industry]))$industry='any';
        $occasion=(string)($p['occasion']??'any');if(!isset(self::OCCASIONS[$occasion]))$occasion='any';
        return array('name'=>$name,'format'=>$format,'category'=>$category,
            'industry'=>$industry,'occasion'=>$occasion,
            'subject'=>$subject,'preheader'=>mb_substr(trim((string)($p['preheader']??'')),0,180),
            'blocks_json'=>json_encode($blocks,JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE),
            'accent'=>$accent,'brand_display'=>$brand,
            'show_unsubscribe'=>isset($p['show_unsubscribe']) && empty($p['show_unsubscribe'])?0:1,
            'cta_label'=>mb_substr(trim((string)($p['cta_label']??'')),0,80),'cta_url'=>$url,
            'base_template_key'=>mb_substr((string)($p['base_template_key']??''),0,30),
            'kind'=>((string)($p['kind']??'custom'))==='prebuilt'?'prebuilt':'custom');
    }

    /** Sample render used by galleries and editors — the same code the send uses. */
    public static function preview(array $row, $storeName, $recipient='Ada') {
        $t=self::normalize($row);
        return self::render($t,array('store'=>$storeName?:'Our store','name'=>$recipient,
            'subject'=>$t['subject'] ?: 'Your subject line goes here','message'=>'We are glad to have you with us. This is where your message reads best — two short paragraphs, then the one thing you want your customer to do.',
            'unsubscribe'=>'https://example.com/unsubscribe'));
    }

    /**
     * Build the email. $ctx carries the merchant's own words: subject, message,
     * the campaign button and the recipient's unsubscribe link.
     */
    public static function render(array $row, array $ctx = array()) {
        $t = self::normalize($row);
        $store = trim((string)($ctx['store'] ?? ''));
        $name = trim((string)($ctx['name'] ?? '')) ?: 'there';
        $subject = (string)($ctx['subject'] ?? '');
        $unsub = self::link($ctx['unsubscribe'] ?? '');
        $tokens = array('{name}'=>self::e($name), '{store}'=>self::e($store),
            '{subject}'=>self::e($subject !== '' ? $subject : $t['subject']));
        $greeting = '<p style="margin:0 0 16px;font-size:16px;line-height:1.7;color:%s">'.self::fill('Hi {name},',$tokens).'</p>';
        $body = ''; $bodyPlain = '';
        foreach (self::chunks($ctx['message'] ?? '') as $chunk) {
            $body .= '<p style="margin:0 0 18px;font-size:16px;line-height:1.75;color:#2a3040">'.nl2br(self::fill($chunk,$tokens)).'</p>';
            $bodyPlain .= '<p style="margin:0 0 16px;font-size:16px;line-height:1.7;color:#25293a">'.nl2br(self::fill($chunk,$tokens)).'</p>';
        }
        $accent = $t['accent'];
        $c = array('t'=>$t,'tokens'=>$tokens,'unsub'=>$unsub,'store'=>$store,
            'accent'=>$accent,'tint'=>self::mix($accent,'#ffffff',.90),'ink'=>self::contrast($accent),
            'label'=>trim((string)($ctx['cta_label'] ?? '')) ?: $t['cta_label'],
            'url'=>self::url($ctx['cta_url'] ?? '') ?: $t['cta_url'],
            'body'=>($body === '' ? '' : sprintf($greeting,'#2a3040').$body),
            'body_plain'=>($bodyPlain === '' ? '' : sprintf($greeting,'#25293a').$bodyPlain));
        return $t['format'] === 'plain' ? self::plain($c) : self::designed($c);
    }

    /** Branded layout: accent band, store mark, blocks, compliance footer. */
    private static function designed(array $c) {
        $t = $c['t'];
        $blocks = '';
        foreach ($t['blocks'] as $block) $blocks .= self::block($block,$c);
        if (trim($blocks) === '') $blocks = '<p style="margin:0;font-size:16px;line-height:1.75;color:#2a3040">'.self::fill('Your message will appear here.',$c['tokens']).'</p>';
        $pre = $t['preheader'] !== '' ? '<div style="display:none;max-height:0;overflow:hidden;opacity:0;font-size:1px;line-height:1px">'.self::fill($t['preheader'],$c['tokens']).'</div>' : '';
        $mark = self::brand($c);
        return '<!doctype html><html lang="en"><head><meta charset="utf-8">'
            .'<meta name="viewport" content="width=device-width,initial-scale=1"><meta http-equiv="X-UA-Compatible" content="IE=edge">'
            .'<title>'.$c['tokens']['{subject}'].'</title></head>'
            .'<body style="margin:0;padding:0;background:#eef1f6">'.$pre
            .'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:#eef1f6"><tr>'
            .'<td align="center" style="padding:28px 12px">'
            .'<table role="presentation" width="640" cellpadding="0" cellspacing="0" style="border-collapse:separate;width:100%;max-width:640px;background:#ffffff;border:1px solid #e4e8f1;border-radius:18px;overflow:hidden">'
            .'<tr><td style="padding:0"><div style="height:6px;background:'.$c['accent'].';font-size:6px;line-height:6px">&nbsp;</div></td></tr>'
            .($mark !== '' ? '<tr><td style="padding:26px 34px 0;font-size:15px;font-weight:bold;color:#1d2333">'.$mark.'</td></tr>' : '')
            .'<tr><td style="padding:'.($mark !== '' ? '20px' : '34px').' 34px 30px">'.$blocks.'</td></tr>'
            .'<tr><td style="padding:22px 34px 26px;background:#f7f8fc;border-top:1px solid #eaedf5;font-size:12px;line-height:1.75;color:#68708a">'.self::footer($c).'</td></tr>'
            .'</table>'
            .'<div style="font-size:11px;color:#8b94a8;padding:16px 4px 0;text-align:center">'.self::fill('{store} sends customer email with MartPoint',$c['tokens']).'</div>'
            .'</td></tr></table></body></html>';
    }

    /** A design that reads like a note from a person, not a marketing page. */
    private static function plain(array $c) {
        $t = $c['t']; $line = 'margin:0 0 16px;font-size:16px;line-height:1.7;color:#25293a'; $blocks = '';
        foreach ($t['blocks'] as $block) {
            $text = trim((string)($block['text'] ?? ''));
            switch ($block['type']) {
                case 'heading': $blocks .= '<p style="margin:0 0 14px;font-size:19px;line-height:1.4;font-weight:bold;color:#1d2333">'.($text !== '' ? self::fill($text,$c['tokens']) : $c['tokens']['{subject}']).'</p>'; break;
                case 'subtitle': case 'paragraph': if ($text !== '') $blocks .= '<p style="'.$line.'">'.nl2br(self::fill($text,$c['tokens'])).'</p>'; break;
                case 'body': $blocks .= $c['body_plain']; break;
                case 'bullets': foreach (preg_split('/\n+/',self::fill($text,$c['tokens'])) as $item) if (trim((string)$item) !== '') $blocks .= '<p style="'.$line.'">&#8226;&nbsp;&nbsp;'.trim((string)$item).'</p>'; break;
                case 'callout': if ($text !== '') $blocks .= '<p style="'.$line.';padding:14px 16px;background:#f4f6fb;border-radius:10px">'.nl2br(self::fill($text,$c['tokens'])).'</p>'; break;
                case 'divider': $blocks .= '<p style="margin:0 0 16px;color:#c3c9d8">&mdash; &mdash; &mdash;</p>'; break;
                case 'terms': if ($text !== '') $blocks .= '<p style="margin:0 0 14px;font-size:13px;line-height:1.6;color:#68708a">'.nl2br(self::fill($text,$c['tokens'])).'</p>'; break;
                case 'button': $blocks .= self::plainButton($block,$c); break;
            }
        }
        if (trim($blocks) === '') $blocks = '<p style="'.$line.'">'.self::fill('Your message will appear here.',$c['tokens']).'</p>';
        return '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            .'<title>'.$c['tokens']['{subject}'].'</title></head><body style="margin:0;padding:0;background:#f6f7f9">'
            .($t['preheader'] !== '' ? '<div style="display:none;max-height:0;overflow:hidden;opacity:0;font-size:1px;line-height:1px">'.self::fill($t['preheader'],$c['tokens']).'</div>' : '')
            .'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:#f6f7f9"><tr>'
            .'<td align="center" style="padding:26px 12px"><table role="presentation" width="620" cellpadding="0" cellspacing="0" style="border-collapse:separate;width:100%;max-width:620px;background:#ffffff;border:1px solid #e6e9f0;border-radius:12px">'
            .'<tr><td style="padding:30px 32px 26px">'.self::brand($c).$blocks.'</td></tr>'
            .'<tr><td style="padding:18px 32px 24px;border-top:1px solid #eceff5;font-size:12px;line-height:1.7;color:#68708a">'.self::footer($c).'</td></tr>'
            .'</table></td></tr></table></body></html>';
    }

    private static function block(array $b, array $c) {
        $tok = $c['tokens']; $text = trim((string)($b['text'] ?? ''));
        switch ($b['type']) {
            case 'heading':
                return '<h1 style="margin:0 0 16px;font-size:29px;line-height:1.22;letter-spacing:-.4px;color:#1d2333">'.($text !== '' ? self::fill($text,$tok) : $tok['{subject}']).'</h1>';
            case 'subtitle':
                return $text === '' ? '' : '<p style="margin:0 0 22px;font-size:17px;line-height:1.6;color:#5a6478">'.self::fill($text,$tok).'</p>';
            case 'body': return $c['body'];
            case 'paragraph':
                return $text === '' ? '' : '<p style="margin:0 0 18px;font-size:16px;line-height:1.75;color:#2a3040">'.nl2br(self::fill($text,$tok)).'</p>';
            case 'bullets':
                $items = '';
                foreach (preg_split('/\n+/',self::fill($text,$tok)) as $item) if (trim((string)$item) !== '') $items .= '<li style="margin:0 0 8px">'.trim((string)$item).'</li>';
                return $items === '' ? '' : '<ul style="margin:0 0 20px;padding:0 0 0 22px;font-size:16px;line-height:1.7;color:#2a3040">'.$items.'</ul>';
            case 'callout':
                return $text === '' ? '' : '<div style="margin:0 0 22px;padding:17px 20px;background:'.$c['tint'].';border-left:4px solid '.$c['accent'].';border-radius:0 12px 12px 0;font-size:16px;line-height:1.65;color:#25304a">'.nl2br(self::fill($text,$tok)).'</div>';
            case 'divider':
                return '<div style="margin:26px 0;border-top:1px solid #e6e9f2;font-size:0;line-height:0">&nbsp;</div>';
            case 'terms':
                return $text === '' ? '' : '<p style="margin:0 0 16px;font-size:12px;line-height:1.65;color:#7b849a">'.nl2br(self::fill($text,$tok)).'</p>';
            case 'button':
                $label = trim((string)($b['label'] ?? '')) !== '' ? trim((string)($b['label'])) : $c['label'];
                $url = self::url($b['url'] ?? '') ?: $c['url'];
                if ($label === '' || $url === '') return '';
                return '<p style="margin:28px 0 6px"><a href="'.self::e($url).'" style="background:'.$c['accent'].';color:'.$c['ink'].';display:inline-block;padding:15px 28px;border-radius:10px;text-decoration:none;font-size:16px;font-weight:bold">'.self::e($label).'</a></p>';
        }
        return '';
    }

    /** Plain designs show the button as a normal underlined link. */
    private static function plainButton(array $b, array $c) {
        $label = trim((string)($b['label'] ?? '')) !== '' ? trim((string)($b['label'])) : $c['label'];
        $url = self::url($b['url'] ?? '') ?: $c['url'];
        if ($label === '' || $url === '') return '';
        return '<p style="margin:0 0 16px;font-size:16px;line-height:1.7"><a href="'.self::e($url).'" style="color:#1d4ed8;font-weight:bold">'.self::e($label).'</a></p>';
    }

    private static function brand(array $c) {
        $t = $c['t'];
        if ($t['brand'] === 'none') return '';
        $name = self::fill('{store}',$c['tokens']);
        if ($t['brand'] === 'initials') {
            return '<span style="display:inline-block;width:38px;height:38px;line-height:38px;text-align:center;background:'.$c['accent'].';color:'.$c['ink'].';border-radius:11px;font-size:15px;font-weight:bold;vertical-align:middle;margin:0 12px 8px 0">'.self::e(self::initials($c['store'])).'</span>'.$name;
        }
        return $name;
    }

    /** Legal footer: who sent it, why the reader is on the list, how to leave. */
    private static function footer(array $c) {
        $t = $c['t'];
        $store = $c['store'] !== '' ? self::e($c['store']) : 'this business';
        $why = $t['category'] === 'welcome'
            ? 'You are receiving this because you started using '.$store.'.'
            : 'You are receiving this because you are on '.$store.'’s mailing list.';
        $out = '<div style="font-weight:bold;color:#4b5468;margin-bottom:5px">'.$store.'</div>'.$why;
        if ($t['unsubscribe'] && $c['unsub'] !== '') $out .= ' <a href="'.self::e($c['unsub']).'" style="color:#4b5468;text-decoration:underline">Unsubscribe</a> from these emails.';
        return $out.'<div style="margin-top:12px;font-size:11px;color:#8b94a8">Sent by '.$store.' with MartPoint</div>';
    }

    private static function chunks($text) {
        $clean = trim(preg_replace('/\r\n|\r/',"\n",(string)$text));
        if ($clean === '') return array();
        $out = array();
        foreach (preg_split('/\n{2,}/',$clean) as $chunk) { $chunk = trim($chunk); if ($chunk !== '') $out[] = $chunk; }
        return $out ?: array($clean);
    }

    /** Escape first, then substitute — merchant words can never become markup. */
    private static function fill($text, array $tokens) { return strtr(self::e((string)$text),$tokens); }

    private static function e($value) { return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8'); }

    private static function accent($value) { return preg_match('/^#[a-f0-9]{6}$/Di',(string)$value)?strtolower($value):self::DEFAULT_ACCENT; }

    /** Only our own site may receive an unsubscribe click. */
    private static function link($value) {
        $value = trim((string)$value);
        if ($value === '' || strlen($value)>1000 || !filter_var($value,FILTER_VALIDATE_URL)) return '';
        return in_array(strtolower((string)parse_url($value,PHP_URL_SCHEME)),array('http','https'),true) ? $value : '';
    }

    /** Campaign and design buttons must be secure destinations. */
    private static function url($value) {
        $value = trim((string)$value);
        if ($value === '' || strlen($value)>1000 || !filter_var($value,FILTER_VALIDATE_URL)) return '';
        return strtolower((string)parse_url($value,PHP_URL_SCHEME)) === 'https' ? $value : '';
    }

    private static function rgb($hex) { $h=ltrim($hex,'#'); return array(hexdec(substr($h,0,2)),hexdec(substr($h,2,2)),hexdec(substr($h,4,2))); }

    private static function mix($from, $to, $ratio) {
        $a = self::rgb($from); $b = self::rgb($to); $out = '';
        for ($i=0;$i<3;$i++) $out .= str_pad(dechex((int)round($a[$i]+($b[$i]-$a[$i])*$ratio)),2,'0',STR_PAD_LEFT);
        return '#'.$out;
    }

    private static function contrast($hex) { $r = self::rgb($hex); return (.299*$r[0]+.587*$r[1]+.114*$r[2])>168 ? '#202331' : '#ffffff'; }

    private static function initials($name) {
        $words = preg_split('/\s+/u',trim((string)$name),-1,PREG_SPLIT_NO_EMPTY);
        $out = '';
        foreach (array_slice($words,0,2) as $word) $out .= mb_strtoupper(mb_substr($word,0,1));
        return $out !== '' ? $out : 'M';
    }

    public static function label(array $map, $key) { return isset($map[$key])?$map[$key]:$key; }

    /** Broad families, so every one of the app's industry presets maps to a design set. */
    public static function family($industryType) {
        $t = strtolower(trim((string)$industryType));
        if ($t === '') return 'any';
        $families = array('health'=>'pharmacy,clinic,hospital,medical,diagnostic,physio,laboratory,dental,eye',
            'food'=>'restaurant,food,cafe,pizza,shawarma,bakery,cake,juice,buka,canteen,butcher,frozen,grocery,provision,convenience,supermarket,mini_mart,market,grill,kitchen,catering,bar',
            'beauty'=>'beauty,salon,barber,makeup,spa,skincare,perfume,laundry,nail,tattoo,hair',
            'fashion'=>'fashion,boutique,shoe,jewel,tailor,cloth,wear,apparel,accessor',
            'tech'=>'electronic,phone,computer,gadget,appliance,scientific,solar,inverter,power',
            'home'=>'furniture,building,paint,garden,hardware,interior,curtain,tile,plumb',
            'learning'=>'school,book,academy,course,train,tutor,educat,library,nursery,university',
            'print'=>'print,stationery,media,sign,advertis,photo',
            'services'=>'service,distributor,wholesal,logistic,transport,consult,legal,financ,bank,insur,estate,hotel,travel,event,clean,repair,garage,workshop,agri,farm');
        foreach ($families as $family=>$needles) foreach (explode(',',$needles) as $needle) if ($needle !== '' && strpos($t,$needle)!==false) return $family;
        return 'retail';
    }
}
