<?php get_header(); ?>
<div style="padding:80px 32px;text-align:center;min-height:60vh;display:flex;flex-direction:column;align-items:center;justify-content:center">
  <div style="font-family:var(--fd);font-size:120px;color:rgba(255,255,255,0.04);line-height:1;margin-bottom:24px">404</div>
  <h1 style="font-family:var(--fd);font-size:48px;margin-bottom:16px">PAGINA NIET GEVONDEN</h1>
  <p style="color:var(--muted);margin-bottom:32px;font-size:14px">De pagina die je zoekt bestaat niet of is verplaatst.</p>
  <div style="display:flex;gap:12px;flex-wrap:wrap;justify-content:center">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="btn-primary">← Terug naar home</a>
    <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="btn-ghost">Bekijk de shop</a>
  </div>
</div>
<?php get_footer(); ?>
