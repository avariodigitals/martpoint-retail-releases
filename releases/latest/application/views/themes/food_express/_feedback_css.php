<style>
/* Load after the shared panels so the restaurant's content rhythm wins. */
body.theme-food_express .mp-product-share{max-width:1180px;margin:0 auto;padding:22px 34px 24px;border:0;border-top:1px solid var(--fx-border);background:#fff}
body.theme-food_express .mp-product-share__label{margin:0 0 12px;color:var(--fx-ink);font-size:15px;font-weight:600}
body.theme-food_express .mp-product-share__actions{display:flex;flex-wrap:wrap;gap:8px}
body.theme-food_express .mp-product-share__button{min-height:44px;padding:10px 15px;border:1px solid var(--fx-border);border-radius:12px;background:#fff;color:var(--fx-ink);font-size:13px;font-weight:500}
body.theme-food_express .mp-product-share__button:hover{background:var(--fx-cream);border-color:var(--fx-green)}
body.theme-food_express .mp-product-share__button:focus-visible{outline:2px solid var(--fx-green);outline-offset:3px}
body.theme-food_express .mp-product-share__status{margin-top:8px;min-height:18px;font-size:12px;color:var(--fx-green);overflow-wrap:anywhere}
body.theme-food_express .fx-review-section{max-width:1180px;padding:28px 34px 40px;margin:0 auto;color:var(--fx-ink);background:#fff}
body.theme-food_express .fx-review-section .mp-product-reviews-head{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:0 0 18px;margin:0 0 4px;border-bottom:1px solid var(--fx-border)}
body.theme-food_express .fx-review-section .mp-product-reviews-head h2{font-family:'DM Sans',sans-serif;font-size:26px;font-weight:700;letter-spacing:-.035em;line-height:1.2}
body.theme-food_express .fx-review-section .mp-product-reviews-score{display:flex;align-items:center;flex-wrap:wrap;justify-content:flex-end;gap:7px;min-width:0;width:auto;border:0;padding:0}
body.theme-food_express .fx-review-section .mp-product-reviews-score strong{font-family:'DM Sans',sans-serif;font-size:13px;font-weight:500;color:var(--fx-muted)}
body.theme-food_express .fx-review-section .mp-product-reviews-score>span{font-size:12px;letter-spacing:0}
body.theme-food_express .fx-review-section .mp-product-reviews-grid{display:block}
body.theme-food_express .fx-review-section .mp-product-review-empty{margin:0;padding:18px 0 24px;font-size:14px;line-height:1.6}
body.theme-food_express .fx-review-section .mp-product-review{padding:22px 0}
body.theme-food_express .fx-review-section .mp-product-review-meta strong{font-family:'DM Sans',sans-serif;font-size:15px}
body.theme-food_express .fx-review-section .mp-product-review h3{font-family:'DM Sans',sans-serif;font-size:17px;line-height:1.3}
body.theme-food_express .fx-review-compose{margin-top:8px;border:1px solid var(--fx-border);border-radius:16px;background:var(--fx-cream);overflow:hidden}
body.theme-food_express .fx-review-compose summary{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:18px 20px;min-height:56px;list-style:none;font-size:14px;font-weight:600;cursor:pointer}
body.theme-food_express .fx-review-compose summary::-webkit-details-marker{display:none}
body.theme-food_express .fx-review-compose summary>span:last-child{font-size:22px;font-weight:400}
body.theme-food_express .fx-review-compose[open] summary>span:last-child{transform:rotate(45deg)}
body.theme-food_express .fx-review-compose summary:focus-visible{outline:2px solid var(--fx-green);outline-offset:-4px}
body.theme-food_express .fx-review-compose .mp-product-review-form{border:0;border-top:1px solid var(--fx-border);border-radius:0;padding:22px 20px;background:#fff;box-shadow:none;gap:10px}
body.theme-food_express .fx-review-compose .mp-product-review-form h3{font-family:'DM Sans',sans-serif;font-size:21px;line-height:1.2;margin:0;letter-spacing:-.02em}
body.theme-food_express .fx-review-help{margin:0 0 8px;color:var(--fx-muted);font-size:12px;line-height:1.6}
body.theme-food_express .fx-review-compose .mp-product-review-form label,body.theme-food_express .fx-review-compose .mp-product-review-form legend{font-size:13px;font-weight:500;letter-spacing:0;text-transform:none}
body.theme-food_express .fx-review-compose .mp-product-review-form input:not([type=radio]),body.theme-food_express .fx-review-compose .mp-product-review-form textarea{padding:12px;min-height:46px;border:1px solid var(--fx-border);border-radius:10px;background:#fff;color:var(--fx-ink)}
body.theme-food_express .fx-review-compose .mp-product-review-form input:focus-visible,body.theme-food_express .fx-review-compose .mp-product-review-form textarea:focus-visible{outline:2px solid var(--fx-green);outline-offset:2px}
body.theme-food_express .fx-review-compose .mp-product-review-contact{grid-template-columns:1fr 1fr;gap:14px}
body.theme-food_express .fx-review-compose .mp-product-review-form button{min-height:48px;border-radius:12px;background:var(--fx-green-deep);font-size:14px;text-transform:none;letter-spacing:0}
body.theme-food_express .fx-review-compose .mp-product-review-rating label{display:flex;align-items:center;justify-content:center;min-width:40px;min-height:44px}
body.theme-food_express .fx-review-compose .mp-product-review-rating span{font-size:30px;color:#b6b1a8;letter-spacing:0}
.mp-product-review-rating label:has(input:checked)~label span{color:#c27a12}
body.theme-food_express .fx-review-compose .mp-product-review-rating label:has(input:checked) span,body.theme-food_express .fx-review-compose .mp-product-review-rating label:has(input:checked)~label span{color:var(--fx-green)}
@media(max-width:820px){
 body.theme-food_express .mp-product-share{padding:22px 18px 16px}
 body.theme-food_express .mp-product-share__actions{display:grid;grid-template-columns:repeat(2,minmax(0,1fr))}
 body.theme-food_express .mp-product-share__button{width:100%;min-width:0}
 body.theme-food_express .fx-review-section{padding:24px 18px 32px}
 body.theme-food_express .fx-review-section .mp-product-reviews-head{flex-direction:row;gap:12px;align-items:center}
 body.theme-food_express .fx-review-section .mp-product-reviews-head h2{font-size:24px}
 body.theme-food_express .fx-review-section .mp-product-reviews-grid{display:block!important}
 body.theme-food_express .fx-review-compose .mp-product-review-contact{grid-template-columns:1fr}
}
</style>
