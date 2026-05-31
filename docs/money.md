# Financial Model & Cash Flow Lifecycle

This document provides a comprehensive blueprint of how money flows through the platform. The financial system is built to be **automated, accurate, and protective of all parties** (the platform, the vendor, and the courier), while maximizing profitability and reducing risk.

---

## 1. How is the Customer Charged? (Customer Delivery Fee)
When a customer adds items to their cart, the system calculates their total bill as follows:
* **Product Cost:** Determined by the vendor.
* **Delivery Fee:** Calculated based **strictly on the distance from the vendor to the customer**.
   - **Smart Hybrid Pricing:** If the vendor and the customer are located within the same "Delivery Zone", the system applies a **fixed Intra-Zone Flat Fee** (e.g., 15 EGP) to encourage local orders.
   - If the delivery crosses into different zones (Cross-Zone), the system switches to **dynamic distance-based pricing** (Base Fee + Rate Per Kilometer).

---

## 2. How is the Courier Paid? (Courier Payout)
To ensure couriers are motivated to accept orders, their payout logic is distinct from what the customer pays:
* Couriers are compensated for their **actual physical effort**: (Distance from the Courier's current location $\rightarrow$ to the Vendor(s) $\rightarrow$ to the Customer).
* If the entire journey takes place within a single Delivery Zone, the courier receives the fixed Intra-Zone Flat Fee (if configured). If they leave the zone at any point, they are paid dynamically based on the total distance.
* **Platform Delivery Margin:** After calculating the gross courier payout, the platform deducts a **Courier Commission** (e.g., 5%), and the remaining amount is the net payout credited to the courier.

---

## 3. How does the Platform earn from Vendors? (Vendor Commission & Hybrid Model)
Once an order is successfully delivered, the `CommissionCalculator` algorithm determines the revenue split:
* The system checks: Is this vendor subscribed to a **paid subscription plan** (Monthly/Yearly)?
   - **Yes (High-Volume Vendor):** The platform applies the reduced commission rate defined by their specific plan (e.g., 5%).
   - **No (Pay-As-You-Go Vendor):** The platform applies the higher default system commission rate (e.g., 12%).
* **Vendor Net Payout** = Total Product Cost - Platform Commission.

---

## 4. Cash Flow During Delivery (COD vs. Online Payments)
Money enters the ecosystem in two distinct ways. The system manages both seamlessly via real-time virtual wallets:

### A. Online Payments (Credit Cards / Digital Wallets)
* The customer pays digitally, and the money lands directly in the **Platform's Central Bank Account**.
* The system automatically credits the "Vendor Net Payout" to the Vendor's virtual wallet (as owed funds).
* The system automatically credits the "Net Courier Payout" to the Courier's virtual wallet (as owed funds).
* **Platform Profit** remains securely in the bank account.

### B. Cash on Delivery (COD)
* The courier collects the physical cash directly from the customer (the courier essentially becomes a "moving vault").
* The courier takes their delivery fee from the cash in their pocket, but they are now holding the (Vendor's Money + Platform's Profit).
* The system records this remaining amount as a **"Debt"** owed by the courier to the platform (represented as a negative wallet balance).

---

## 5. System Protections & Risk Management
Because COD operations involve freelance couriers holding company cash, the system is equipped with automated safety nets:
* **Cash Hold Limit:** Every courier has a maximum credit ceiling (e.g., 2,000 EGP). The moment the cash debt in their pocket hits this limit, the system **automatically blocks them** from receiving any new COD orders until they deposit the cash to the company.
* **Real-time Auto-Offsetting:** Suppose a courier owes the platform 200 EGP from previous COD orders. They then complete an Online Paid order where their delivery fee is 50 EGP. The system intelligently intercepts this 50 EGP and deducts it from their debt. Their new debt balance becomes 150 EGP.

---

## 6. Settlements & Payouts (Console Commands)
Although wallet balances are updated in **real-time** with every order, the final financial reconciliation is handled periodically via backend scheduled jobs (Console Commands) like `ProcessCourierSettlements` and `ProcessVendorSettlements`.

**Why are final settlements not done instantly after every order?**
1. **Batching (Minimizing Bank Transfers):** An active vendor might process 50 orders a day. Processing 50 separate bank transfers per day is financially inefficient and creates accounting chaos. Settlement commands aggregate a week's worth of real-time transactions into a single clean "Settlement Invoice" for one consolidated bank transfer.
2. **Dispute Window:** Generating an immediate settlement invoice blocks the funds. Delaying the settlement (e.g., by 24-48 hours) provides a crucial buffer window to handle customer complaints, refunds, or courier disputes before the funds are finalized for external transfer.
3. **Courier Shift Closing:** For couriers holding physical cash, you cannot reconcile their accounts while they are actively driving. The settlement command runs at the end of the cycle (e.g., midnight) to aggregate all cash collected and digital earnings, issuing one final statement: "You must drop off X amount at the office."
4. **System Performance:** Aggregating invoices, calculating auto-deductions, and finalizing taxes are heavy database operations. Deferring them to background cron jobs ensures the core application remains lightning-fast during peak order hours.

Once a Settlement Invoice is generated and approved by the admin (after transferring the money or receiving the cash drop-off), the user's wallet balance is zeroed out, and a new financial cycle begins.
