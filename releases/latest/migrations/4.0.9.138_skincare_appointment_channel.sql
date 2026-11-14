-- A skincare treatment is requested without taking an online payment.
-- Reuse the existing tenant-scoped online order and service fulfilment flow,
-- with a distinct channel so it is never represented as delivery or WhatsApp.
ALTER TABLE `db_online_orders`
  MODIFY COLUMN `payment_method` ENUM('paystack','whatsapp','pay_on_delivery','appointment')
  DEFAULT 'pay_on_delivery';
