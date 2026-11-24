<?php
$steps=array_values(array_filter(array_map('trim',preg_split('/\r\n|\r|\n/',(string)($how_to_order['steps']??''))),function($line){return $line!=='';}));
$image=trim((string)($how_to_order['image_url']??''));
$video=trim((string)($how_to_order['video_url']??''));
$home=base_url('store/'.rawurlencode($settings->store_slug??''));
?>
<main class="mp-how-order" style="max-width:900px;margin:0 auto;padding:clamp(24px,5vw,64px) 20px 72px;color:var(--mp-text,#18221d)">
  <a href="<?= htmlspecialchars($home,ENT_QUOTES,'UTF-8'); ?>" style="display:inline-block;margin-bottom:22px;color:var(--mp-primary,#24553c);text-decoration:none">← Back to store</a>
  <h1 style="font-size:clamp(30px,5vw,48px);line-height:1.1;margin:0 0 24px"><?= htmlspecialchars($how_to_order['title']??'How to order',ENT_QUOTES,'UTF-8'); ?></h1>
  <?php if($image!==''): ?><img src="<?= htmlspecialchars($image,ENT_QUOTES,'UTF-8'); ?>" alt="How to order" style="display:block;width:100%;max-height:460px;object-fit:cover;border-radius:16px;margin-bottom:28px"><?php endif; ?>
  <?php if($steps): ?><ol style="padding-left:26px;margin:0 0 30px;line-height:1.8;font-size:17px"><?php foreach($steps as $step): ?><li style="padding:0 0 12px 6px"><?= htmlspecialchars($step,ENT_QUOTES,'UTF-8'); ?></li><?php endforeach; ?></ol><?php else: ?><p style="line-height:1.8">Please contact the store if you need help placing your order.</p><?php endif; ?>
  <?php if($video!==''): ?><p><a href="<?= htmlspecialchars($video,ENT_QUOTES,'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-block;padding:13px 20px;border-radius:999px;background:var(--mp-primary,#24553c);color:#fff;text-decoration:none;font-weight:600">▶ Watch how to order</a></p><?php endif; ?>
</main>
