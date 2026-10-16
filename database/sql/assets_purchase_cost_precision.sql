-- Allow asset invoice amounts of 10,000,000 and above.
-- assets.purchase_cost was DECIMAL(10,3), which rejects those amounts
-- when a Capex GRN is approved and converted into assets.
-- Import in phpMyAdmin, then approve the GRN again.

ALTER TABLE `assets` MODIFY `purchase_cost` DECIMAL(18,3) NULL;
