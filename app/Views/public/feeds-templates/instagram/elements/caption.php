<?php defined('ABSPATH') or die;

if ($caption) { ?>
    <div class="npfeed-ig-post-caption">
        <p class="npfeed-ig-post-caption-text">
            <?php
            $npfeed_caption = preg_replace(
                "/#([\p{L}\p{N}_]+)/u",
                '<a class="npfeed-ig-post-caption-tags" href="https://www.instagram.com/explore/tags/$1" target="_blank">#$1</a>',
                nl2br($caption)
            );
            echo wp_kses_post($npfeed_caption);
            ?>
        </p>
    </div>
<?php }