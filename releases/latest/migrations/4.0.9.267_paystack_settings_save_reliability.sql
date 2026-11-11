-- Version marker for the Paystack settings save/readback and bank-lookup
-- reliability update. The PHP changes are backward-compatible and need no
-- schema alteration; this statement lets installed fleets advance normally.
SELECT 1;
