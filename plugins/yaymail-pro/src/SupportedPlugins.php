<?php
namespace YayMail;

use YayMail\Utils\SingletonTrait;
use YayMail\Utils\Logger;
/**
 * YayMail SupportedPlugins
 *
 * @method static SupportedPlugins get_instance()
 */
class SupportedPlugins {

    use SingletonTrait;

    private $logger;

    private $wc_emails               = [];
    private $addon_supported_plugins = [];

    private function __construct() {
        $this->logger = new Logger();

        $this->wc_emails = \WC_Emails::instance()->get_emails();

        $this->addon_supported_plugins = [
            'YayMailAddonWcSubscription'                   => [
                'plugin_name'            => 'WooCommerce Subscriptions',
                'template_ids'           => $this->get_template_ids(
                    [
                        'ENR_Email_Customer_Auto_Renewal_Reminder',
                        'ENR_Email_Customer_Expiry_Reminder',
                        'ENR_Email_Customer_Manual_Renewal_Reminder',
                        'ENR_Email_Customer_Processing_Shipping_Fulfilment_Order',
                        'ENR_Email_Customer_Shipping_Frequency_Notification',
                        'ENR_Email_Customer_Subscription_Price_Updated',
                        'ENR_Email_Customer_Trial_Ending_Reminder',
                        'WCS_Email_Completed_Renewal_Order',
                        'WCS_Email_Cancelled_Subscription',
                        'WCS_Email_Completed_Switch_Order',
                        'WCS_Email_Customer_Payment_Retry',
                        'WCS_Email_Customer_Renewal_Invoice',
                        'WCS_Email_Expired_Subscription',
                        'WCS_Email_New_Renewal_Order',
                        'WCS_Email_New_Switch_Order',
                        'WCS_Email_Customer_On_Hold_Renewal_Order',
                        'WCS_Email_On_Hold_Subscription',
                        'WCS_Email_Payment_Retry',
                        'WCS_Email_Processing_Renewal_Order',
                        'WCS_Email_Customer_Notification_Auto_Renewal',
                        'WCS_Email_Customer_Notification_Auto_Trial_Expiration',
                        'WCS_Email_Customer_Notification_Manual_Renewal',
                        'WCS_Email_Customer_Notification_Manual_Trial_Expiration',
                        'WCS_Email_Customer_Notification_Subscription_Expiration',
                    ]
                ),
                'slug_name'              => 'woocommerce-subscriptions',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-woocommerce-subscriptions',
                'is_3rd_party_installed' => class_exists( 'WC_Subscriptions' ),
            ],
            'YayMailAddonYITHWishlist'                     => [
                // yith_wishlist_constructor
                'plugin_name'            => 'YITH WooCommerce Wishlist',
                'template_ids'           => $this->get_template_ids(
                    [
                        'yith_wcwl_back_in_stock',
                        'estimate_mail',
                        'yith_wcwl_on_sale_item',
                        'yith_wcwl_promotion_mail',
                    ]
                ),
                'slug_name'              => 'yith-woocommerce-wishlist-premium',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-yith-woocommerce-wishlist',
                'is_3rd_party_installed' => function_exists( 'yith_wishlist_constructor' ),

            ],
            'YayMailAddonSUMOSubscriptions'                => [
                'plugin_name'            => 'SUMO Subscription',
                'template_ids'           => $this->get_template_ids(
                    [
                        'SUMOSubs_Subscription_Auto_Renewal_Reminder_Email',
                        'SUMOSubs_Subscription_Auto_Renewal_Success_Email',
                        'SUMOSubs_Subscription_Cancelled_Email',
                        'SUMOSubs_Subscription_Cancel_Request_Revoked_Email',
                        'SUMOSubs_Subscription_Cancel_Request_Submitted_Email',
                        'SUMOSubs_Subscription_Order_Completed_Email',
                        'SUMOSubs_Subscription_Expired_Email',
                        'SUMOSubs_Subscription_Expiry_Reminder_Email',
                        'SUMOSubs_Subscription_Invoice_Email',
                        'SUMOSubs_Subscription_New_Order_Email',
                        'SUMOSubs_Subscription_New_Order_Old_Subscribers_Email',
                        'SUMOSubs_Subscription_Paused_Email',
                        'SUMOSubs_Subscription_Overdue_Automatic_Email',
                        'SUMOSubs_Subscription_Overdue_Manual_Email',
                        'SUMOSubs_Subscription_Pending_Authorization_Email',
                        'SUMOSubs_Subscription_Order_Processing_Email',
                        'SUMOSubs_Subscription_Suspended_Automatic_Email',
                        'SUMOSubs_Subscription_Suspended_Manual_Email',
                        'SUMOSubs_Subscription_Turnoff_Auto_Payments_Success_Email',
                    ]
                ),
                'slug_name'              => 'sumosubscriptions',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-sumo-subscription',
                'is_3rd_party_installed' => class_exists( 'SUMOSubscriptions' ),

            ],
            'YayMailAddonYITHWooSubscription'              => [
                'plugin_name'            => 'YITH WooCommerce Subscription Premium',
                'template_ids'           => $this->get_template_ids(
                    [
                        'YITH_WC_Customer_Subscription_Before_Expired',
                        'YITH_WC_Customer_Subscription_Cancelled',
                        'YITH_WC_Customer_Subscription_Expired',
                        'YITH_WC_Customer_Subscription_Paused',
                        'YITH_WC_Customer_Subscription_Payment_Done',
                        'YITH_WC_Customer_Subscription_Payment_Failed',
                        'YITH_WC_Customer_Subscription_Request_Payment',
                        'YITH_WC_Customer_Subscription_Renew_Reminder',
                        'YITH_WC_Customer_Subscription_Resumed',
                        'YITH_WC_Subscription_Status',
                        'YITH_WC_Customer_Subscription_Suspended',
                    ]
                ),
                'slug_name'              => 'yith-woocommerce-subscription-premium',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-yith-woocommerce-subscription',
                'is_3rd_party_installed' => function_exists( 'YITH_WC_Subscription' ),

            ],
            'YayMailAddonWcB2B'                            => [
                'plugin_name'            => 'WooCommerce B2B',
                'template_ids'           => $this->get_template_ids(
                    [
                        'WCB2B_Email_Customer_OnQuote_Order',
                        'WCB2B_Email_Customer_Quoted_Order',
                        'WCB2B_Email_Customer_Status_Notification',
                        'WCB2B_Email_New_Quote',
                    ]
                ),
                'slug_name'              => 'woocommerce-b2b',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-woocommerce-b2b',
                'is_3rd_party_installed' => class_exists( 'WooCommerceB2B' ),

            ],
            'YayMailYithVendor'                            => [
                'plugin_name'            => 'YITH WooCommerce Multi Vendor Premium',
                'template_ids'           => $this->get_template_ids(
                    [
                        'YITH_WC_Email_Cancelled_Order',
                        'YITH_WC_Email_Commissions_Paid',
                        'YITH_WC_Email_Commissions_Unpaid',
                        'YITH_WC_Email_New_Order',
                        'YITH_WC_Email_New_Staff_Member',
                        'YITH_WC_Email_New_Vendor_Registration',
                        'YITH_WC_Email_Product_Set_In_Pending_Review',
                        'YITH_WC_Email_Vendor_Commissions_Bulk_Action',
                        'YITH_WC_Email_Vendor_Commissions_Paid',
                        'YITH_WC_Email_Vendor_New_Account',
                    ]
                ),
                'slug_name'              => 'yith-woocommerce-multi-vendor-premium',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-yith-woocommerce-multi-vendor',
                'is_3rd_party_installed' => function_exists( 'YITH_Vendors' ),
            ],
            'YayMailAddonGermanized'                       => [
                'plugin_name'            => 'Germanized Pro',
                'template_ids'           => $this->get_template_ids(
                    [
                        'storeabill_documentadmin',
                        'storeabill_vendiderogermanizedprostoreabillpackingslipemail',
                        'WC_GZD_Email_Customer_Cancelled_Order',
                        'WC_GZD_Email_Customer_Guest_Return_Shipment_Request',
                        'WC_GZD_Email_Customer_New_Account_Activation',
                        'WC_GZD_Email_Customer_Paid_For_Order',
                        'storeabill_cancellationinvoice',
                        'storeabill_document',
                        'storeabill_simpleinvoice',
                        'WC_GZD_Email_Customer_Return_Shipment',
                        'WC_GZD_Email_Customer_Return_Shipment_Delivered',
                        'WC_GZD_Email_Customer_Revocation',
                        'WC_GZD_Email_Customer_Shipment',
                        'WC_GZD_Email_New_Return_Shipment_Request',
                        'oss_woocommerce_deliverythresholdemailnotification',
                        'WC_GZD_Email_Customer_SEPA_Direct_Debit_Mandate',
                        'WC_STC_Email_Customer_Guest_Return_Shipment_Request',
                        'WC_STC_Email_Customer_Return_Shipment',
                        'WC_STC_Email_Customer_Return_Shipment_Delivered',
                        'WC_STC_Email_Customer_Shipment',
                        'WC_STC_Email_New_Return_Shipment_Request',

                    ]
                ),
                'slug_name'              => 'woocommerce-germanized',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-woocommerce-germanized',
                'is_3rd_party_installed' => class_exists( 'WooCommerce_Germanized' ),

            ],
            'YayMailAddonWcBookings'                       => [
                'plugin_name'            => 'WooCommerce Bookings',
                'template_ids'           => $this->get_template_ids(
                    [
                        'WC_Email_Admin_Booking_Cancelled',
                        'WC_Email_Booking_Cancelled',
                        'WC_Email_Booking_Confirmed',
                        'WC_Email_Booking_Notification',
                        'WC_Email_Booking_Pending_Confirmation',
                        'WC_Email_Booking_Reminder',
                        'WC_Email_New_Booking',
                    ]
                ),
                'slug_name'              => 'woocommerce-bookings',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-woocommerce-bookings',
                'is_3rd_party_installed' => class_exists( 'WC_Bookings' ),

            ],
            'YayMailAddonWcWaitlist'                       => [
                'plugin_name'            => 'WooCommerce Waitlist',
                'template_ids'           => $this->get_template_ids(
                    [
                        'Pie_WCWL_Waitlist_Joined_Email',
                        'Pie_WCWL_Waitlist_Left_Email',
                        'Pie_WCWL_Waitlist_Mailout',
                        'Pie_WCWL_Waitlist_Signup_Email',
                    ]
                ),
                'slug_name'              => 'woocommerce-waitlist',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-woocommerce-waitlist',
                'is_3rd_party_installed' => class_exists( 'WooCommerce_Waitlist_Plugin' ),

            ],
            'YayMailAddonQuotesForWooCommerce'             => [
                'plugin_name'            => 'Quotes for WooCommerce',
                'template_ids'           => $this->get_template_ids(
                    [
                        'QWC_Request_New_Quote',
                        'QWC_Request_Sent',
                        'QWC_Send_Quote',
                    ]
                ),
                'slug_name'              => 'quotes-for-woocommerce',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-quotes-for-woocommerce',
                'is_3rd_party_installed' => class_exists( 'Quotes_WC' ),

            ],
            'YayMailAddonYITHPreOrder'                     => [
                'plugin_name'            => 'YITH Pre-Order',
                'template_ids'           => $this->get_template_ids(
                    [
                        'YITH_Pre_Order_New_Pre_Order_Email',
                        'YITH_Pre_Order_Out_Of_Stock_Email',
                        'YITH_Pre_Order_Payment_Reminder_Email',
                        'YITH_Pre_Order_Cancelled_Email',
                        'YITH_Pre_Order_Completed_Email',
                        'YITH_Pre_Order_Confirmed_Email',
                        'YITH_Pre_Order_Release_Date_Changed_Email',
                        'YITH_Pre_Order_Release_Date_Reminder_Email',
                    ]
                ),
                'slug_name'              => 'yith-woocommerce-pre-order-premium',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-yith-woocommerce-pre-order',
                'is_3rd_party_installed' => function_exists( 'yith_ywpo_init' ),

            ],
            'YayMailAddonWCAppointments'                   => [
                'plugin_name'            => 'WooCommerce Appointments',
                'template_ids'           => $this->get_template_ids(
                    [
                        'WC_Email_Admin_Appointment_Cancelled',
                        'WC_Email_Admin_Appointment_Rescheduled',
                        'WC_Email_Admin_New_Appointment',
                        'WC_Email_Appointment_Cancelled',
                        'WC_Email_Appointment_Confirmed',
                        'WC_Email_Appointment_Follow_Up',
                        'WC_Email_Appointment_Reminder',
                    ]
                ),
                'slug_name'              => 'woocommerce-appointments',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-woocommerce-appointments',
                'is_3rd_party_installed' => class_exists( 'WC_Appointments' ),

            ],
            'YayMailAddonSGWcOrderApproval'                => [
                'plugin_name'            => 'SG WooCommerce Order Approval',
                'template_ids'           => $this->get_template_ids(
                    [
                        'Sgitsoa_WC_Admin_Order_New',
                        'WC_Admin_Order_New',
                        'Sgitsoa_WC_Customer_Order_Approved',
                        'WC_Customer_Order_Approved',
                        'Sgitsoa_WC_Customer_Order_New',
                        'WC_Customer_Order_New',
                        'Sgitsoa_WC_Customer_Order_Rejected',
                        'WC_Customer_Order_Rejected',
                    ]
                ),
                'slug_name'              => 'sg-order-approval-woocommerce-pro',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-sg-woocommerce-order-approval',
                'is_3rd_party_installed' => class_exists( 'Sgitsoa_Order_Approval_Woocommerce_Pro' ) || class_exists( 'Sg_Order_Approval_Woocommerce' ),
            ],
            'YayMailAddonWFU'                              => [
                'plugin_name'            => 'WooCommerce Follow Up',
                'template_ids'           => $this->get_follow_up_email_ids(),
                'slug_name'              => 'woocommerce-follow-up-emails',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-woocommerce-follow-up-emails',
                'is_3rd_party_installed' => function_exists( 'FUE' ),

            ],
            'YayMailAddonOrderDeliveryDatePro'             => [
                'plugin_name'            => 'Order Delivery Date Pro',
                'template_ids'           => $this->get_template_ids(
                    [
                        'ORDDD_Email_Admin_Delivery_Reminder',
                        'ORDDD_Email_Update_Date',
                        'ORDDD_Email_Delivery_Reminder',
                        'ORDDD_Lite_Email_Update_Date',
                    ]
                ),
                'slug_name'              => 'order-delivery-date',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-woocommerce-order-delivery-date',
                'is_3rd_party_installed' => class_exists( 'order_delivery_date' ) || class_exists( 'order_delivery_date_lite' ),

            ],
            'YayMailAddonOrderCancellationEmailToCustomer' => [
                'plugin_name'            => 'Order Cancellation Email to Customer',
                'template_ids'           => $this->get_template_ids(
                    [
                        'WC_Email_Cancelled_customer_Order',
                    ]
                ),
                'slug_name'              => 'order-cancellation-email-to-customer',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-order-cancellation-email-to-customer',
                'is_3rd_party_installed' => class_exists( 'KA_Custom_WC_Email' ),

            ],
            'YayMailAddonWcSmartCoupons'                   => [
                'plugin_name'            => 'WooCommerce Smart Coupons',
                'template_ids'           => $this->get_template_ids(
                    [
                        'WC_SC_Combined_Email_Coupon',
                        'WC_SC_Acknowledgement_Email',
                        'WC_SC_Email_Coupon',
                    ]
                ),
                'slug_name'              => 'woocommerce-smart-coupons',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-smart-coupons',
                'is_3rd_party_installed' => class_exists( 'WC_Smart_Coupons' ),

            ],
            'YayMailAddonDokan'                            => [
                'plugin_name'            => 'Dokan',
                'template_ids'           => $this->get_template_ids(
                    [
                        'Announcement',
                        'CanceledRefundVendor',
                        'ContactSeller',
                        'ConversationNotification',
                        'DokanEmailBookingCancelled',
                        'Dokan_Email_Booking_New',
                        'Dokan_Email_Wholesale_Register',
                        'Dokan_Follow_Store_Email',
                        'Dokan_Follow_Store_Vendor_Email',
                        'Dokan_New_Support_Ticket',
                        'DokanNewSupportTicketForAdmin',
                        'Dokan_Product_Enquiry_Email',
                        'DokanReplyToAdminSupportTicket',
                        'Dokan_Reply_To_Store_Support_Ticket',
                        'Dokan_Reply_To_User_Support_Ticket',
                        'Dokan_Report_Abuse_Admin_Email',
                        'Dokan_Rma_Send_Warranty_Request',
                        'Dokan_Send_Coupon_Email',
                        'Dokan_Staff_New_Order',
                        'Dokan_Staff_Password_Update',
                        'InvoiceAuthentication',
                        'InvoiceEmail',
                        'Dokan_Email_New_Product',
                        'Dokan_Email_New_Product_Pending',
                        'Dokan_Email_New_Seller',
                        'Dokan_Email_New_Store_Review',
                        'Dokan_Email_Product_Published',
                        'Dokan_Email_Refund_Request',
                        'Dokan_Email_Refund_Vendor',
                        'Dokan_Vendor_Verification_Request_Submission',
                        'Dokan_Email_Reverse_Withdrawal_Invoice',
                        'Dokan_Email_Shipping_Status',
                        'Dokan_Vendor_Verification_Status_Update',
                        'Dokan_Email_Admin_Update_Order_Delivery_Time',
                        'Dokan_Email_Updated_Product',
                        'Dokan_Email_Update_Request_Quote',
                        'Dokan_Email_Vendor_Update_Order_Delivery_Time',
                        'Dokan_Email_Completed_Order',
                        'Dokan_Email_Vendor_Disable',
                        'Dokan_Email_Vendor_Enable',
                        'Dokan_Email_New_Order',
                        'Dokan_Email_Vendor_Product_Review',
                        'Dokan_Email_Vendor_Withdraw_Request',
                        'Dokan_Email_Withdraw_Approved',
                        'Dokan_Email_Withdraw_Cancelled',
                        'Dokan_Email_New_Request_Quote',
                        'Dokan_Email_Accept_Request_Quote',
                        'Dokan_Email_Vendor_Coupon_Updated',
                        'Dokan_Email_Marked_Order_Received',
                        'Dokan_Email_Staff_Add_Notification',
                    ]
                ),
                'slug_name'              => [ 'dokan-lite', 'dokan-pro' ],
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-dokan',
                'is_3rd_party_installed' => class_exists( 'WeDevs_Dokan' ),

            ],
            'YayMailAddonGermanMarket'                     => [
                'plugin_name'            => 'Woocommerce_German_Market',
                'template_ids'           => [
                    'wgm_confirm_order_email',
                    'wgm_double_opt_in_customer_registration',
                    'wgm_sepa',
                ],
                'slug_name'              => 'woocommerce-german-market',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-german-market',
                'is_3rd_party_installed' => class_exists( 'Woocommerce_German_Market' ),

            ],
            'YayMailAddonB2BWholesaleSuite'                => [
                'plugin_name'            => 'B2B & Wholesale Suite',
                'template_ids'           => $this->get_template_ids(
                    [
                        'B2bwhs_Your_Account_Approved_Email',
                        'B2bwhs_New_Customer_Email',
                        'B2bwhs_New_Customer_Requires_Approval_Email',
                        'B2bwhs_New_Message_Email',
                        'B2bwhs_New_Quote_Email',
                    ]
                ),
                'slug_name'              => 'b2b-wholesale-suite',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-b2b-wholesale-suite',
                'is_3rd_party_installed' => class_exists( 'B2bwhs' ),

            ],
            'YayMailAddonWcDeposits'                       => [
                'plugin_name'            => 'WooCommerce Deposits',
                'template_ids'           => $this->get_template_ids(
                    [
                        'WC_Deposits_Email_Customer_Deposit_Paid',
                        'WC_Deposits_Email_Full_Payment',
                        'WC_Deposits_Email_Customer_Partial_Payment_Paid',
                        'WC_Deposits_Email_Partial_Payment',
                        'WC_Deposits_Email_Customer_Remaining_Reminder',
                    ]
                ),
                'slug_name'              => 'woocommerce-deposits',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-woocommerce-deposits',
                'is_3rd_party_installed' => class_exists( '\Webtomizer\WCDP\WC_Deposits' ),
            ],
            'YayMailAddonYITHWooBookingAndAppointment'     => [
                'plugin_name'            => 'YITH Booking and Appointment for WooCommerce Premium',
                'template_ids'           => $this->get_template_ids(
                    [
                        'YITH_WCBK_Email_Booking_Status',
                        'YITH_WCBK_Email_Admin_New_Booking',
                        'YITH_WCBK_Email_Customer_Booking_Note',
                        'YITH_WCBK_Email_Customer_Booking_Notification_After_End',
                        'YITH_WCBK_Email_Customer_Booking_Notification_After_Start',
                        'YITH_WCBK_Email_Customer_Booking_Notification_Before_End',
                        'YITH_WCBK_Email_Customer_Booking_Notification_Before_Start',
                        'YITH_WCBK_Email_Customer_Cancelled_Booking',
                        'YITH_WCBK_Email_Customer_Completed_Booking',
                        'YITH_WCBK_Email_Customer_Confirmed_Booking',
                        'YITH_WCBK_Email_Customer_New_Booking',
                        'YITH_WCBK_Email_Customer_Paid_Booking',
                        'YITHEmailCustomerUnconfirmedBooking',
                    ]
                ),
                'slug_name'              => 'yith-woocommerce-booking-premium',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-yith-booking-and-appointment-for-woocommerce',
                'is_3rd_party_installed' => function_exists( 'yith_wcbk_init' ),

            ],
            'YayMailAddonPointsRewards'                    => [
                'plugin_name'            => 'Points and Rewards for WooCommerce',
                'template_ids'           => $this->get_template_ids(
                    [
                        'wps_wpr_email_notification',
                    ]
                ),
                'slug_name'              => 'points-and-rewards-for-woocommerce',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-points-and-rewards-for-woocommerce',
                'is_3rd_party_installed' => class_exists( 'Points_Rewards_For_Woocommerce' ),

            ],
            'YayMailAddonWcGiftCards'                      => [
                'plugin_name'            => 'WooCommerce Gift Cards',
                'template_ids'           => $this->get_template_ids(
                    [
                        'WC_GC_Email_Gift_Card_Received',
                        'WC_GC_Email_Gift_Card_Send_To_Buyer',
                        'WC_GC_Email_Expiration_Reminder',
                    ]
                ),
                'slug_name'              => 'woocommerce-gift-cards',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-woocommerce-gift-cards',
                'is_3rd_party_installed' => class_exists( 'WC_Gift_Cards' ),

            ],
            'YayMailAddonPWGC'                             => [
                'plugin_name'            => 'PW WooCommerce Gift Cards',
                'template_ids'           => [ 'pwgc_email' ],
                'slug_name'              => 'pw-woocommerce-gift-cards',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-woocommerce-gift-cards',
                'is_3rd_party_installed' => class_exists( 'PW_Gift_Cards' ),

            ],
            'YayMailAddonYITHWooGiftCards'                 => [
                'plugin_name'            => 'YITH WooCommerce Gift Cards',
                'template_ids'           => $this->get_template_ids(
                    [
                        'ywgc-email-delivered-gift-card',
                        'ywgc-email-send-gift-card',
                        'ywgc-email-notify-customer',
                    ]
                ),
                'slug_name'              => 'yith-woocommerce-gift-cards-premium',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-yith-woocommerce-gift-cards',
                'is_3rd_party_installed' => function_exists( 'YITH_YWGC' ),

            ],
            'YayMailAddonYITHWooMembership'                => [
                'plugin_name'            => 'YITH WooCommerce Membership',
                'template_ids'           => $this->get_template_ids(
                    [
                        'YITH_WCMBS_Cancelled_Mail',
                        'YITH_WCMBS_Expired_Mail',
                        'YITH_WCMBS_Expiring_Mail',
                        'YITH_WCMBS_Welcome_Mail',
                    ]
                ),
                'slug_name'              => 'yith-woocommerce-membership-premium',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-yith-woocommerce-membership',
                'is_3rd_party_installed' => function_exists( 'yith_wcmbs_pr_init' ),

            ],
            'YayMailAddonOrderDeliveryWc'                  => [
                'plugin_name'            => 'WooCommerce Order Delivery',
                'template_ids'           => $this->get_template_ids(
                    [
                        'WC_OD_Email_Order_Delivery_Note',
                        'WC_OD_Email_Subscription_Delivery_Note',
                    ]
                ),
                'slug_name'              => 'woocommerce-order-delivery',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-order-delivery-for-woocommerce',
                'is_3rd_party_installed' => class_exists( 'WC_Order_Delivery' ),

            ],
            'YayMailAddonWcSimpleAuctions'                 => [
                'plugin_name'            => 'WooCommerce Simple Auction',
                'template_ids'           => [
                    'bid_note',
                    'auction_buy_now',
                    'auction_closing_soon',
                    'auction_fail',
                    'auction_finished',
                    'auction_relist',
                    'auction_relist_user',
                    'remind_to_pay',
                    'auction_win',
                    'customer_bid_note',
                    'Reserve_fail',
                    'outbid_note',
                ],
                'slug_name'              => 'woocommerce-simple-auctions',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-woocommerce-simple-auction',
                'is_3rd_party_installed' => class_exists( 'WooCommerce_simple_auction' ),

            ],
            'YayMailAddonWCVendors'                        => [
                'plugin_name'            => 'WooCommerce Vendors Marketplace',
                'template_ids'           => $this->get_template_ids(
                    [
                        'WCVendors_Admin_Notify_Application',
                        'WCVendors_Admin_Notify_Approved',
                        'WCVendors_Admin_Notify_Product',
                        'WCVendors_Admin_Notify_Shipped',
                        'WC_Email_Approve_Vendor',
                        'WC_Vendors_Pro_Email_Customer_Mark_Received',
                        'WCVendors_Customer_Notify_Shipped',
                        'WC_Email_Notify_Admin',
                        'WC_Email_Notify_Vendor',
                        'WCVendors_Vendor_Notify_Application',
                        'WCVendors_Vendor_Notify_Approved',
                        'WCVendors_Vendor_Notify_Cancelled_Order',
                        'WCVendors_Vendor_Notify_Denied',
                        'WCVendors_Vendor_Notify_Order',
                        'WC_Email_Notify_Shipped',
                        'WC_Vendors_Pro_Email_Vendor_Contact_Widget',
                    ]
                ),
                'slug_name'              => [ 'wc-vendors', 'wc-vendors-pro' ],
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-wcvendors',
                'is_3rd_party_installed' => class_exists( 'WC_Vendors' ),

            ],
            'YayMailAddonWcPreOrders'                      => [
                'plugin_name'            => 'WooCommerce Pre-Orders',
                'template_ids'           => $this->get_template_ids(
                    [
                        'WC_Pre_Orders_Email_Admin_Pre_Order_Cancelled',
                        'WC_Pre_Orders_Email_New_Pre_Order',
                        'WC_Pre_Orders_Email_Pre_Order_Available',
                        'WC_Pre_Orders_Email_Pre_Order_Cancelled',
                        'WC_Pre_Orders_Email_Pre_Order_Date_Changed',
                        'WC_Pre_Orders_Email_Pre_Ordered',
                    ]
                ),
                'slug_name'              => 'woocommerce-pre-orders',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-woocommerce-pre-order',
                'is_3rd_party_installed' => class_exists( 'WC_Pre_Orders' ),

            ],
            'YayMailWooSplitOrders'                        => [
                'plugin_name'            => 'WooCommerce Split Orders',
                'template_ids'           => $this->get_template_ids(
                    [
                        'Customer_Order_Split',
                    ]
                ),
                'slug_name'              => 'split-orders',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-woocommerce-split-order',
                'is_3rd_party_installed' => function_exists( 'vibe_split_orders' ),

            ],
            'YayMailAddonWPCrowdfunding'                   => [
                'plugin_name'            => 'WP Crowdfunding Pro',
                'template_ids'           => $this->get_template_ids(
                    [
                        'WPCF_Campaign_Accept',
                        'WPCF_Campaign_Submit',
                        'WPCF_Campaign_Update',
                        'WPCF_New_Backed',
                        'WPCF_New_User',
                        'WPCF_Target_Reached',
                        'WPCF_Withdraw_Request',
                    ]
                ),
                'slug_name'              => 'wp-crowdfunding-pro',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-wp-crowdfunding',
                'is_3rd_party_installed' => class_exists( '\WPCF_PRO\Init' ),

            ],
            'YayMailAddonWcPIP'                            => [
                'plugin_name'            => 'WC Print Invoices/Packing Lists',
                'template_ids'           => $this->get_template_ids(
                    [
                        'pip_email_invoice',
                        'pip_email_packing_list',
                        'pip_email_pick_list',
                    ]
                ),
                'slug_name'              => 'woocommerce-pip',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-woocommerce-print-invoices-packing-lists',
                'is_3rd_party_installed' => class_exists( 'WC_PIP' ),

            ],
            'YayMailAddonLicenseManagerWc'                 => [
                'plugin_name'            => 'License Manager for WooCommerce',
                'template_ids'           => $this->get_template_ids(
                    [
                        'LMFWC_Customer_Deliver_License_Keys',
                    ]
                ),
                'slug_name'              => 'license-manager-for-woocommerce',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-license-manager',
                'is_3rd_party_installed' => class_exists( 'LicenseManagerForWooCommerce\\Main' ),

            ],
            'YayMailAddonWcAccountFunds'                   => [
                'plugin_name'            => 'Account Funds',
                'template_ids'           => $this->get_template_ids(
                    [
                        'WC_Account_Funds_Email_Account_Funds_Increase',
                    ]
                ),
                'slug_name'              => 'woocommerce-account-funds',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-account-funds',
                'is_3rd_party_installed' => class_exists( 'WC_Account_Funds' ),

            ],
            'YayMailAddonAutomateWoo'                      => [
                'plugin_name'            => 'AutomateWoo',
                'template_ids'           => $this->get_automatewoo_template_ids(),
                'slug_name'              => 'automatewoo',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-automatewoo',
                'is_3rd_party_installed' => class_exists( 'AutomateWoo_Loader' ),

            ],
            'YayMailAddonSMFW'                             => [
                'plugin_name'            => 'ShopMagic',
                'template_ids'           => $this->get_shopmagic_template_ids(),
                'slug_name'              => 'shopmagic-for-woocommerce',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-shopmagic',
                'is_3rd_party_installed' => class_exists( 'WPDesk\ShopMagic\Workflow\Workflow' ),

            ],
            'YayMailAddonWcStripePaymentGateway'           => [
                'plugin_name'            => 'WooCommerce Stripe Payment Gateway',
                'template_ids'           => $this->get_template_ids(
                    [
                        'WC_Stripe_Email_Failed_Authentication_Retry',
                        'WC_Stripe_Email_Failed_Preorder_Authentication',
                        'WC_Stripe_Email_Failed_Renewal_Authentication',
                    ]
                ),
                'slug_name'              => 'woocommerce-gateway-stripe',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-woocommerce-stripe-payment-gateway',
                'is_3rd_party_installed' => class_exists( 'WC_Stripe' ),

            ],
            'YayMailAddonYithStripePremium'                => [
                'plugin_name'            => 'YITH WooCommerce Stripe Premium',
                'template_ids'           => $this->get_template_ids(
                    [
                        'YITH_WCStripe_Expiring_Card_Email',
                        'YITH_WCStripe_Renew_Needs_Action_Email',
                    ]
                ),
                'slug_name'              => 'yith-woocommerce-stripe-premium',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-yith-woocommerce-stripe',
                'is_3rd_party_installed' => function_exists( 'YITH_WCStripe' ),

            ],
            'YayMailAddonWcfmMarketplace'                  => [
                'plugin_name'            => 'WooCommerce Multivendor Marketplace',
                'template_ids'           => $this->get_wcfmvm_template_ids(),
                'slug_name'              => [ 'wc-multivendor-marketplace', 'wc-frontend-manager', 'wc-multivendor-membership' ],
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-wcfm-marketplace',
                'is_3rd_party_installed' => class_exists( 'WCFMmp' ),

            ],
            'YayMailAddonWcMemberships'                    => [
                'plugin_name'            => 'WooCommerce Memberships',
                'template_ids'           => $this->get_template_ids(
                    [
                        'WC_Memberships_User_Membership_Activated_Email',
                        'WC_Memberships_User_Membership_Ended_Email',
                        'WC_Memberships_User_Membership_Ending_Soon_Email',
                        'WC_Memberships_User_Membership_Note_Email',
                        'WC_Memberships_User_Membership_Renewal_Reminder_Email',
                        'wc_memberships_for_teams_team_invitation',
                        'wc_memberships_for_teams_team_membership_ended',
                        'wc_memberships_for_teams_team_membership_ending_soon',
                        'wc_memberships_for_teams_team_membership_renewal_reminder',
                    ]
                ),
                'slug_name'              => [ 'woocommerce-memberships', 'woocommerce-memberships-for-teams' ],
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-woocommerce-memberships',
                'is_3rd_party_installed' => class_exists( 'WC_Memberships' ),

            ],
            'YayMailAddonWcTrackShip'                      => [
                'plugin_name'            => 'TrackShip for WooCommerce',
                'template_ids'           => [
                    'trackship_available_for_pickup',
                    'trackship_delivered',
                    'trackship_exception',
                    'trackship_failure',
                    'trackship_in_transit',
                    'trackship_on_hold',
                    'trackship_out_for_delivery',
                    'trackship_pickup_reminder',
                    'trackship_return_to_sender',

                ],
                'slug_name'              => 'trackship-for-woocommerce',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-trackship-for-woocommerce',
                'is_3rd_party_installed' => class_exists( 'Trackship_For_Woocommerce' ),

            ],
            // TODO: Hold
            'AliDropship_Woo_Plugin'                       => [
                'plugin_name'            => 'AliDropship Woo Plugin',
                'template_ids'           => [
                    'adsw_order_shipped_notification',
                    'adsw_order_tracking_changed_notification',
                    'adsw_update_notification',
                ],
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-alidropship-woo-plugin',
                'is_3rd_party_installed' => false,

            ],
            'YayMailYITHWooReviewDiscountsPremium'         => [
                'plugin_name'            => 'YITH WooCommerce Review For Discounts Premium',
                'template_ids'           => $this->get_template_ids(
                    [
                        'YWRFD_Coupon_Mail',
                    ]
                ),
                'slug_name'              => 'yith-woocommerce-review-for-discounts-premium',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-yith-review-for-discounts',
                'is_3rd_party_installed' => function_exists( 'YITH_WRFD' ),

            ],
            'YayMailAddonSUMOPaymentPlans'                 => [
                'plugin_name'            => 'SUMO Payment Plans',
                'template_ids'           => $this->get_template_ids(
                    [
                        'SUMO_PP_Deposit_Balance_Payment_Auto_Charge_Reminder_Email',
                        'SUMO_PP_Deposit_Balance_Payment_Completed_Email',
                        'SUMO_PP_Deposit_Balance_Payment_Invoice_Email',
                        'SUMO_PP_Deposit_Balance_Payment_Overdue_Email',
                        'SUMO_PP_Payment_Awaiting_Cancel_Email',
                        'SUMO_PP_Payment_Cancelled_Email',
                        'SUMO_PP_Payment_Pending_Auth_Email',
                        'SUMO_PP_Payment_Plan_Auto_Charge_Reminder_Email',
                        'SUMO_PP_Payment_Plan_Completed_Email',
                        'SUMO_PP_Payment_Plan_Invoice_Email',
                        'SUMO_PP_Payment_Plan_Overdue_Email',
                        'SUMO_PP_Payment_Plan_Success_Email',
                        'SUMO_PP_Payment_Schedule_Email',
                    ]
                ),
                'slug_name'              => 'sumopaymentplans',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-sumo-payment-plans',
                'is_3rd_party_installed' => class_exists( 'SUMOPaymentPlans' ),

            ],
            'YayMailAddonWcTeraWallet'                     => [
                'plugin_name'            => 'TeraWallet',
                'template_ids'           => $this->get_template_ids(
                    [
                        'Woo_Wallet_Email_Low_Wallet_Balance',
                        'Woo_Wallet_Email_New_Transaction',
                        'WOO_Wallet_Withdrawal_Approved',
                        'WOO_Wallet_Withdrawal_Reject',
                        'WOO_Wallet_Withdrawal_Request',
                    ]
                ),
                'slug_name'              => 'woo-wallet',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-terawallet',
                'is_3rd_party_installed' => class_exists( 'WooWallet' ) || class_exists( 'Woo_Wallet' ),

            ],
            // TODO
            'CustomFieldsforWooCommerce'                   => [
                'plugin_name'  => 'Custom Fields for WooCommerce by Addify',
                'template_ids' => [
                    'af_email_admin_register_new_user',
                    'af_email_approve_user_account',
                    'af_email_declined_user_account',
                    'af_email_register_new_account',
                ],
                'link_upgrade' => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-custom-fields-by-addify',
            ],
            'YayMailAddonMultiLocationInventory'           => [
                'plugin_name'            => 'WooCommerce MultiLocation Inventory & Order Routing',
                'template_ids'           => $this->get_template_ids(
                    [
                        'WC_Wh_New_Order_Email',
                        'WC_Wh_Reassign_Order_Email',
                    ]
                ),
                'slug_name'              => 'myworks-warehouse-routing',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-woocommerce-multi-warehouse-order-routing',
                'is_3rd_party_installed' => class_exists( 'MW_WHDependencies' ),

            ],
            'YayMailAddonMultiVendorX'                     => [
                'plugin_name'            => 'MultiVendorX - The Ultimate WooCommerce Multivendor Marketplace Solution',
                'template_ids'           => $this->get_template_ids(
                    [
                        'WC_Email_Admin_Added_New_Product_to_Vendor',
                        'WC_Email_Admin_Change_Order_Status',
                        'WC_Email_Admin_New_Question',
                        'WC_Email_Admin_New_Vendor_Account',
                        'WC_Email_Admin_Widthdrawal_Request',
                        'WC_Email_Approved_New_Vendor_Account',
                        'WC_Email_Customer_Refund_Request',
                        'WC_Email_Customer_Answer',
                        'WC_Email_Vendor_New_Coupon_Added',
                        'WC_Email_Plugin_Deactivated_Mail',
                        'WC_Email_Rejected_New_Vendor_Account',
                        'WC_Email_Send_Report_Abuse',
                        'WC_Email_Send_Site_Information',
                        'WC_Email_Suspend_Vendor_Account',
                        'WC_Email_Admin_Vendor_Account_Deactivation_Request_Mail',
                        'WC_Email_Vendor_Account_Deactive_Request_Reject_Mail',
                        'WC_Email_Vendor_Account_Deletion_Mail',
                        'WC_Email_Vendor_DirectBank_Commission_Transactions',
                        'WC_Email_Vendor_Cancelled_Order',
                        'WC_Email_Vendor_Direct_Bank',
                        'WC_Email_Vendor_Contact_Widget',
                        'WC_Email_Vendor_Followed',
                        'WC_Email_Vendor_Followed_Customer',
                        'WC_Email_Vendor_New_Account',
                        'WC_Email_Vendor_New_Announcement',
                        'WC_Email_Vendor_New_Coupon_Added_To_Customer',
                        'WC_Email_Vendor_New_Order',
                        'WC_Email_Vendor_New_Product_Added',
                        'WC_Email_Vendor_New_Question',
                        'WC_Email_Notify_Shipped',
                        'WC_Email_Vendor_Orders_Stats_Report',
                        'WC_Email_Vendor_Product_Approved',
                        'WC_Email_Vendor_Product_Rejected',
                        'WC_Email_Vendor_Review',
                        'WC_Email_Vendor_Commission_Transactions',
                    ]
                ),
                'slug_name'              => [ 'dc-woocommerce-multi-vendor', 'mvx-pro' ],
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-wc-marketplace',
                'is_3rd_party_installed' => class_exists( 'WC_Dependencies_Product_Vendor' ),

            ],
            'YayMailAddonAffiliateWc'                      => [
                'plugin_name'            => 'Affiliate For WooCommerce',
                'template_ids'           => $this->get_template_ids(
                    [
                        'AFWC_Email_Affiliate_Pending_Request',
                        'AFWC_Email_Affiliate_Summary_Reports',
                        'AFWC_Email_Automatic_Payouts_Reminder',
                        'AFWC_Email_Commission_Paid',
                        'AFWC_Email_New_Conversion_Received',
                        'AFWC_Email_New_Registration_Received',
                        'AFWC_Email_Welcome_Affiliate',
                    ]
                ),
                'slug_name'              => 'affiliate-for-woocommerce',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-affiliate-for-woocommerce',
                'is_3rd_party_installed' => class_exists( 'Affiliate_For_WooCommerce' ),

            ],
            'YayMailAddonWooProductVendors'                => [
                'plugin_name'            => 'WooCommerce Product Vendors',
                'template_ids'           => $this->get_template_ids(
                    [
                        'WC_Product_Vendors_Approval',
                        'WC_Product_Vendors_Cancelled_Order_Email_To_Vendor',
                        'WC_Product_Vendors_New_Renewal_Email_To_Vendor',
                        'WC_Product_Vendors_Order_Email_To_Vendor',
                        'WC_Product_Vendors_Order_Fulfill_Status_To_Admin',
                        'WC_Product_Vendors_Order_Note_To_Customer',
                        'WC_Product_Vendors_Product_Added_Notice',
                        'WC_Product_Vendors_Registration_Email_To_Admin',
                        'WC_Product_Vendors_Registration_Email_To_Vendor',
                    ]
                ),
                'slug_name'              => 'woocommerce-product-vendors',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-woocommerce-product-vendors',
                'is_3rd_party_installed' => class_exists( 'WC_Product_Vendors' ),
            ],
            'YayMailAddonBISN'                             => [
                'plugin_name'            => 'WooCommerce Back In Stock Notifications',
                'template_ids'           => $this->get_template_ids(
                    [
                        'WC_BIS_Email_Notification_Confirm',
                        'WC_BIS_Email_Notification_Received',
                        'WC_BIS_Email_Notification_Verify',
                    ]
                ),
                'slug_name'              => 'woocommerce-back-in-stock-notifications',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-back-in-stock-notifications',
                'is_3rd_party_installed' => class_exists( 'WC_Back_In_Stock' ),

            ],
            'YayMailAddonWcReturnWarranty'                 => [
                'plugin_name'            => 'WooCommerce Return and Warrranty Pro',
                'template_ids'           => $this->get_template_ids(
                    [
                        'WCRW_Send_Coupon_Email',
                        'WCRW_Send_Message_Email',
                        'WCRW_Cancel_Order_Request',
                        'WCRW_Create_Request_Admin',
                        'WCRW_Create_Request_Customer',
                        'WCRW_Update_Request',
                    ]
                ),
                'slug_name'              => [ 'wc-return-warrranty', 'wc-return-warranty-pro' ],
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-woocommerce-return-and-warranty',
                'is_3rd_party_installed' => class_exists( 'WC_Return_Warranty' ) || class_exists( 'WC_Return_Warranty_Pro' ),

            ],
            'YayMailAddonB2BKing'                          => [
                'plugin_name'            => 'B2BKing',
                'template_ids'           => $this->get_template_ids(
                    [
                        'B2bking_New_Customer_Email',
                        'B2bking_New_Customer_Requires_Approval_Email',
                        'B2bking_New_Message_Email',
                        'B2bking_New_Offer_Email',
                        'B2bking_Your_Account_Approved_Email',
                    ]
                ),
                'slug_name'              => 'b2bking',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-b2bking',
                'is_3rd_party_installed' => class_exists( 'B2bking' ),

            ],
            // TODO
            // 'Domina_Shipping'                              => [
            // 'plugin_name'            => 'Domina Shipping',
            // 'template_ids'           => [
            // 'Domina_Email_Tracking',
            // ],
            // 'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-domina-shipping',
            // 'is_3rd_party_installed' => class_exists( 'B2bking' ),

            // ],
            'YayMailAddonYITHWooDeliveryDate'              => [
                'plugin_name'            => 'YITH WooCommerce Delivery Date Premium',
                'template_ids'           => $this->get_template_ids(
                    [
                        'YITH_Delivery_Date_Advise_Customer_Email',
                    ]
                ),
                'slug_name'              => 'yith-woocommerce-delivery-date-premium',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-yith-woocommerce-delivery-date',
                'is_3rd_party_installed' => function_exists( 'yith_delivery_date_init_plugin' ),
            ],
            'YayMailAddonYITHAdvancedRefundSystem'         => [
                'plugin_name'            => 'YITH Advanced Refund System for WooCommerce Premium',
                'template_ids'           => $this->get_template_ids(
                    [
                        'YITH_ARS_Coupon_User_Email',
                        'YITH_ARS_New_Message_Admin_Email',
                        'YITH_ARS_New_Message_User_Email',
                        'YITH_ARS_New_Request_Admin_Email',
                        'YITH_ARS_New_Request_User_Email',
                        'YITH_ARS_Approved_User_Email',
                        'YITH_ARS_On_Hold_User_Email',
                        'YITH_ARS_Processing_User_Email',
                        'YITH_ARS_Rejected_User_Email',
                    ]
                ),
                'slug_name'              => 'yith-advanced-refund-system-for-woocommerce.premium',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-yith-advanced-refund-system',
                'is_3rd_party_installed' => function_exists( 'yith_ywars_init' ),
            ],
            'YayMailAddonYITHWooAffiliates'                => [
                'plugin_name'            => 'YITH WooCommerce Affiliates Premium',
                'template_ids'           => $this->get_template_ids(
                    [
                        'YITH_WCAF_admin_affiliate_banned_Email',
                        'YITH_WCAF_admin_affiliate_status_changed_Email',
                        'YITH_WCAF_admin_commission_status_changed_Email',
                        'YITH_WCAF_admin_new_affiliate_Email',
                        'YITH_WCAF_admin_paid_commission_Email',
                        'YITH_WCAF_affiliate_banned_Email',
                        'YITH_WCAF_affiliate_disabled_Email',
                        'YITH_WCAF_affiliate_enabled_Email',
                        'YITH_WCAF_new_affiliate_Email',
                        'YITH_WCAF_new_affiliate_commission_Email',
                        'YITH_WCAF_new_affiliate_coupon_Email',
                        'YITH_WCAF_new_affiliate_payment_Email',
                    ]
                ),
                'slug_name'              => 'yith-woocommerce-affiliates-premium',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-yith-woocommerce-affiliates',
                'is_3rd_party_installed' => function_exists( 'yith_affiliates_constructor' ),
            ],
            'YayMailAddonYITHWooAuctions'                  => [
                'plugin_name'            => 'YITH Auctions for WooCommerce Premium',
                'template_ids'           => $this->get_template_ids(
                    [
                        'YITH_WCACT_Email_Auction_No_Winner',
                        'YITH_WCACT_Email_Auction_Rescheduled_Admin',
                        'YITH_WCACT_Email_Auction_Winner',
                        'YITH_WCACT_Email_Auction_Winner_Reminder',
                        'YITH_WCACT_Email_Better_Bid',
                        'YITH_WCACT_Email_Closed_Buy_Now',
                        'YITH_WCACT_Email_Delete_Bid',
                        'YITH_WCACT_Email_Delete_Bid_Admin',
                        'YITH_WCACT_Email_End_Auction',
                        'YITH_WCACT_Email_New_Bid',
                        'YITH_WCACT_Email_Not_Reached_Reserve_Price',
                        'YITH_WCACT_Email_Not_Reached_Reserve_Price_Max_Bidder',
                        'YITH_WCACT_Email_Successfully_Bid',
                        'YITH_WCACT_Email_Successfully_Bid_Admin',
                        'YITH_WCACT_Email_Successfully_Follow',
                        'YITH_WCACT_Email_Winner_Admin',
                        'YITH_WCACT_Email_Without_Bid',
                    ]
                ),
                'slug_name'              => 'yith-woocommerce-auctions-premium',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-yith-auctions-for-woocommerce',
                'is_3rd_party_installed' => function_exists( 'yith_wcact_init_premium' ),
            ],
            'YayMailAddonRMAReturnRefundExchange'          => [
                'plugin_name'            => 'RMA Return Refund & Exchange for WooCommerce',
                'template_ids'           => $this->get_template_ids(
                    [
                        'wps_rma_cancel_request_email',
                        'wps_rma_exchange_request_accept_email',
                        'wps_rma_exchange_request_cancel_email',
                        'wps_rma_exchange_request_email',
                        'wps_rma_order_messages_email',
                        'wps_rma_refund_email',
                        'wps_rma_refund_request_accept_email',
                        'wps_rma_refund_request_cancel_email',
                        'wps_rma_refund_request_email',
                        'wps_rma_returnship_email',
                    ]
                ),
                'slug_name'              => [ 'woo-refund-and-exchange-lite', 'woocommerce-rma-for-return-refund-and-exchange' ],
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-rma-return-refund-and-exchange-for-woocommerce',
                'is_3rd_party_installed' => function_exists( 'define_woo_refund_and_exchange_lite_constants' ),

            ],
            'YayMailAddonYITHWooPointsRewards'             => [
                'plugin_name'            => 'YITH WooCommerce Points and Rewards Premium',
                'template_ids'           => $this->get_template_ids(
                    [
                        'YITH_YWPAR_Expiration',
                        'YITH_YWPAR_Update_Points',
                    ]
                ),
                'slug_name'              => 'yith-woocommerce-points-and-rewards-premium',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-yith-points-and-rewards',
                'is_3rd_party_installed' => function_exists( 'yith_ywpar_premium_constructor' ),

            ],
            'YayMailAddonWCPDFProductVouchers'             => [
                'plugin_name'            => 'WooCommerce PDF Product Vouchers',
                'template_ids'           => $this->get_template_ids(
                    [
                        'WC_PDF_Product_Vouchers_Email_Voucher_Purchaser',
                        'WC_PDF_Product_Vouchers_Email_Voucher_Recipient',
                    ]
                ),
                'slug_name'              => 'woocommerce-pdf-product-vouchers',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-woocommerce-pdf-product-vouchers',
                'is_3rd_party_installed' => class_exists( 'WC_PDF_Product_Vouchers' ),

            ],
            'YayMailAddonYITHWooRequestAQuote'             => [
                'plugin_name'            => 'YITH WooCommerce Request A Quote Premium',
                'template_ids'           => $this->get_template_ids(
                    [
                        'YITH_YWRAQ_Quote_Status',
                        'YITH_YWRAQ_Send_Quote',
                        'YITH_YWRAQ_Send_Quote_Reminder',
                        'YITH_YWRAQ_Send_Quote_Reminder_Accept',
                        'YITH_YWRAQ_Send_Email_Request_Quote',
                        'YITH_YWRAQ_Send_Email_Request_Quote_Customer',
                    ]
                ),
                'slug_name'              => 'yith-woocommerce-request-a-quote-premium',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-yith-woocommerce-request-a-quote',
                'is_3rd_party_installed' => function_exists( 'yith_ywraq_premium_constructor' ),

            ],
            'YayMailAddonWooSellServices'                  => [
                'plugin_name'            => 'Woo Sell Services',
                'template_ids'           => $this->get_template_ids(
                    [
                        'WC_Order_Accepted_Email',
                        'WC_Order_Rejected_Email',
                        'WC_Requirement_Received_Email',
                        'WC_Order_Ready_Email',
                        'WC_Requirement_Order_Email',
                        'WC_Order_Conversation_Email',
                    ]
                ),
                'slug_name'              => 'woo-sell-services',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-woo-sell-services',
                'is_3rd_party_installed' => class_exists( 'Woo_Sell_Services_Main' ),

            ],
            'YayMailAddonYITHWooRecoverAbandonedCart'      => [
                'plugin_name'            => 'YITH WooCommerce Recover Abandoned Cart Premium',
                'template_ids'           => $this->get_template_ids(
                    [
                        'YITH_YWRAC_Send_Email',
                        'YITH_YWRAC_Send_Email_Recovered_Cart',
                    ]
                ),
                'slug_name'              => 'yith-woocommerce-recover-abandoned-cart-premium',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-yith-woocommerce-recover-abandoned-cart',
                'is_3rd_party_installed' => function_exists( 'yith_ywrac_premium_constructor' ),
            ],
            'YayMailAddonYITHWooCouponEmailSystem'         => [
                'plugin_name'            => 'YITH WooCommerce Coupon Email System Premium',
                'template_ids'           => [
                    'YWCES_birthday',
                    'YWCES_first_purchase',
                    'YWCES_last_purchase',
                    'YWCES_product_purchasing',
                    'YWCES_purchases',
                    'YWCES_register',
                    'YWCES_spending',
                    'yith-coupon-email-system',
                ],
                'slug_name'              => 'yith-woocommerce-coupon-email-system-premium',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-yith-woocommerce-coupon-email-system',
                'is_3rd_party_installed' => function_exists( 'ywces_init' ),
            ],
            'YayMailAddonYITHWooEasyLoginAndRegisterPopup' => [
                'plugin_name'            => 'YITH Easy Login & Register Popup For WooCommerce',
                'template_ids'           => $this->get_template_ids(
                    [
                        'YITH_WELRP_Customer_Authentication_Code',
                    ]
                ),
                'slug_name'              => 'yith-easy-login-register-popup-for-woocommerce',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-yith-easy-login-register-popup-for-woo',
                'is_3rd_party_installed' => function_exists( 'yith_welrp_init' ),

            ],
            'YayMailAddonColissimoShippingMethod'          => [
                'plugin_name'            => 'Colissimo shipping methods for WooCommerce',
                'template_ids'           => $this->get_template_ids(
                    [
                        'LpcInwardLabelGenerationEmail',
                        'LpcOutwardLabelGenerationEmail',
                    ]
                ),
                'slug_name'              => 'colissimo-shipping-methods-for-woocommerce',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-colissimo-shipping-methods',
                'is_3rd_party_installed' => class_exists( 'LpcInit' ),

            ],
            'YayMailAddonParcelPanelOrderTrackingWc'       => [
                'plugin_name'            => 'Parcel Panel Order Tracking for WooCommerce',
                'template_ids'           => $this->get_template_ids(
                    [
                        'WC_Email_Customer_Partial_Shipped_Order',
                        'WC_Email_Customer_PP_Delivered',
                        'WC_Email_Customer_PP_Exception',
                        'WC_Email_Customer_PP_Failed_Attempt',
                        'WC_Email_Customer_PP_In_Transit',
                        'WC_Email_Customer_PP_Out_For_Delivery',
                    ]
                ),
                'slug_name'              => 'parcelpanel',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-parcelpanel-order-tracking-for-woocommerce/',
                'is_3rd_party_installed' => class_exists( 'ParcelPanel\ParcelPanel' ),

            ],
            // TODO change link
            'YayMailAddonWcCartAbandonmentRecovery'        => [
                'plugin_name'            => 'WooCommerce Cart Abandonment Recovery',
                'template_ids'           => $this->get_wc_cart_abandonment_recovery_template_ids(),
                'slug_name'              => 'woo-cart-abandonment-recovery',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-woo-cart-abandonment-recovery-cartflows',
                'is_3rd_party_installed' => class_exists( 'CARTFLOWS_CA_Loader' ),

            ],
            'YayMailAddonB2BMarket'                        => [
                'plugin_name'            => 'B2B Market',
                'template_ids'           => [
                    'new_customer_registration_admin_customer_approval',
                    'new_customer_registration_pending_approval',
                    'new_customer_registration_user_approved',
                    'new_customer_registration_user_denied',
                    'double_opt_in_customer_registration',
                ],
                'slug_name'              => 'b2b-market',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-b2b-market-marketpress',
                'is_3rd_party_installed' => class_exists( 'BM' ),

            ],
            'YayMailAddonWholesaleX'                       => [
                'plugin_name'            => 'WholesaleX',
                'template_ids'           => $this->get_template_ids(
                    [
                        'WholesaleX_Admin_New_User_Awating_Approval_Notification_Email',
                        'WholesaleX_Admin_New_User_Notification_Email',
                        'WholesaleX_New_User_Approved_Email',
                        'WholesaleX_New_User_Auto_Approved_Email',
                        'WholesaleX_New_User_Pending_For_Approval_Email',
                        'WholesaleX_New_User_Rejected_Email',
                        'WholesaleX_New_User_Verification_Email',
                        'WholesaleX_New_User_Verified_Email',
                        'WholesaleX_User_Profile_Update_Notification_Email',
                        'WholesaleX_New_Subaccount_Create_Email',
                        'WholesaleX_Subaccount_Order_Approval_Required_Email',
                        'WholesaleX_Subaccount_Order_Approved_Email',
                        'WholesaleX_Subaccount_Order_Pending_Email',
                        'WholesaleX_Subaccount_Order_Placed_Email',
                        'WholesaleX_Subaccount_Order_Reject_Email',
                    ]
                ),
                'slug_name'              => 'wholesalex',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-wholesalex',
                'is_3rd_party_installed' => function_exists( 'wholesalex_run' ),

            ],
            'YayMailAddonWcBookingsAppointments'           => [
                'plugin_name'            => 'Bookings and Appointments For WooCommerce Premium',
                'template_ids'           => $this->get_template_ids(
                    [
                        'Ph_WC_Email_Booking_Cancelled_For_Admin',
                        'Ph_WC_Email_Booking_Waiting_For_Approval',
                        'Ph_WC_Email_Booking_Cancelled',
                        'Ph_WC_Email_Booking_Confirmation',
                        'Ph_WC_Email_Booking_followup',
                        'Ph_WC_Email_Booking_reminder',
                        'Ph_WC_Email_Booking_Requires_Confirmation',
                    ]
                ),
                'slug_name'              => 'ph-bookings-appointments-woocommerce-premium-3.4.2',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-woocommerce-booking-and-appointments-pluginhive',
                'is_3rd_party_installed' => class_exists( 'phive_booking_initialze_premium' ),

            ],
            'YayMailAddonWcContactShippingQuote'           => [
                'plugin_name'            => 'WooCommerce Contact for Shipping Quote',
                'template_ids'           => $this->get_template_ids(
                    [
                        'WCCSQ_Email_Customer_Shipping_Quote_Available',
                        'WCCSQ_Email_Shipping_Quote_Requested',
                    ]
                ),
                'slug_name'              => 'woocommerce-contact-for-shipping-quote',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-woocommerce-contact-for-shipping-quote',
                'is_3rd_party_installed' => is_plugin_active( 'woocommerce-contact-for-shipping-quote/plugin.php' ),

            ],
            'YayMailAddonDepositsPartialPayment'           => [
                'plugin_name'            => 'Deposits & Partial Payments for WooCommerce',
                'template_ids'           => $this->get_template_ids(
                    [
                        'AWCDP_Email_Deposit_Paid',
                        'AWCDP_Email_Full_Payment',
                        'AWCDP_Email_Partial_Paid',
                        'AWCDP_Email_Partial_Payment',
                        'AWCDP_Email_Payment_Reminder',
                    ]
                ),
                'slug_name'              => 'deposits-partial-payments-for-woocommerce-pro',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-woocommerce-deposits-partial-payments-acowebs/',
                'is_3rd_party_installed' => class_exists( 'AWCDP_Deposits' ),

            ],
            'YayMailAddonMarketKing'                       => [
                'plugin_name'            => 'MarketKing Core',
                'template_ids'           => $this->get_template_ids(
                    [
                        'Marketking_New_Announcement_Email',
                        'Marketking_New_Message_Email',
                        'Marketking_New_Payout_Email',
                        'Marketking_New_Product_Requires_Approval_Email',
                        'Marketking_New_Rating_Email',
                        'Marketking_New_Refund_Email',
                        'Marketking_New_Vendor_Requires_Approval_Email',
                        'Marketking_New_Verification_Email',
                        'Marketking_Product_Has_Been_Approved_Email',
                        'Marketking_Your_Account_Approved_Email',
                    ]
                ),
                'slug_name'              => [ 'marketking-multivendor-marketplace-for-woocommerce', 'marketking-pro' ],
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-marketking-multivendor-marketplace-plugin',
                'is_3rd_party_installed' => class_exists( 'Marketkingcore' ),

            ],
            'YayMailAddonAddifyRegistrationFields'         => [
                'plugin_name'            => 'Custom User Registration Fields for WooCommerce',
                'template_ids'           => $this->get_template_ids(
                    [
                        'afreg_admin_email_new_user',
                        'afreg_admin_email_update_user',
                        'afreg_approved_user_email_user',
                        'afreg_disapproved_user_email_user',
                        'afreg_pending_user_email_user',
                        'afreg_user_email_new_user',
                    ]
                ),
                'slug_name'              => 'user-registration-plugin-for-woocommerce',
                'link_upgrade'           => 'https://yaycommerce.com/yaymail-addons/yaymail-addon-for-custom-user-registration-fields-for-woocommerce-by-addify/',
                'is_3rd_party_installed' => class_exists( 'Addify_Registration_Fields_Addon' ),
            ],
        ];

        // Temporarily prevent checking 3rd templates counting for addons.
        // $this->addon_supported_plugins = array_filter(
        // $this->addon_supported_plugins,
        // fn( $addon ) =>
        // count( $addon['template_ids'] ) > 0 || $addon['plugin_name'] === 'AutomateWoo' || $addon['plugin_name'] === 'ShopMagic'
        // );
    }


    public function get_template_ids_from_core() {
        return $this->get_template_ids(
            [
                'WC_Email_Cancelled_Order',
                'WC_Email_Customer_Completed_Order',
                'WC_Email_Customer_Invoice',
                'WC_Email_Customer_New_Account',
                'WC_Email_Customer_Note',
                'WC_Email_Customer_On_Hold_Order',
                'WC_Email_Customer_Processing_Order',
                'WC_Email_Customer_Refunded_Order',
                'WC_Email_Customer_Reset_Password',
                'WC_Email_Failed_Order',
                'WC_Email_Customer_Failed_Order',
                'WC_Email_New_Order',
            ]
        );
    }

    private function get_template_ids( array $template_names ): array {

        return array_filter(
            array_map(
                function ( $template_name ) {
                    return $this->wc_emails[ $template_name ]->id ?? null;
                },
                $template_names
            )
        );
    }

    private function get_follow_up_email_ids() {
        if ( ! is_callable( 'fue_get_emails' ) ) {
            return [];
        }

        $follow_ups_emails = \fue_get_emails( 'any', [ 'fue-active' ] );
        $follow_ups_emails = array_filter(
            $follow_ups_emails,
            function ( $email ) {
                return $email->status === 'fue-active';
            }
        );

        if ( empty( $follow_ups_emails ) ) {
            return [];
        }

        return array_map(
            function ( $fue_email ) {
                return 'follow_up_email_' . $fue_email->id;
            },
            $follow_ups_emails
        );
    }

    private function get_automatewoo_template_ids() {
        if ( ! class_exists( 'AutomateWoo\Workflow_Query' ) ) {
            return [];
        }

        $query = new \AutomateWoo\Workflow_Query();
        $query->set_return( 'ids' );
        $ids = $query->get_results();

        if ( empty( $ids ) ) {
            return [];
        }

        $workflows = [];

        foreach ( $ids as $id ) {
            $workflow = \AutomateWoo\Workflows\Factory::get( $id );
            if ( $workflow ) {
                $workflows[] = $workflow;
            }
        }

        $template_ids = [];

        foreach ( $workflows as $workflow ) {
            $actions = $workflow->get_actions();
            foreach ( $actions as $action_index => $action ) {
                $workflow_id = $workflow->get_id();
                $name        = 'AutomateWoo_' . $workflow_id;
                if ( $action_index !== null ) {
                    $name .= '_action_' . $action_index;
                }
                $template_ids[] = $name;
            }
        }

        return $template_ids;
    }

    private function get_shopmagic_template_ids() {
        if ( ! class_exists( 'YayMailAddonSMFW\Emails\EmailsCreation' ) ) {
            return [];
        }

        $emails = \YayMailAddonSMFW\Emails\EmailsCreation::get_instance()->get_emails();

        if ( empty( $emails ) ) {
            return [];
        }

        return array_filter(
            array_map(
                function ( $email ) {
                    return $email->get_id();
                },
                $emails
            )
        );
    }

    private function get_wcfmvm_template_ids() {
        if ( ! function_exists( 'get_wcfmvm_emails' ) ) {
            return [];
        }
        $emails = get_wcfmvm_emails();
        return array_filter( array_keys( $emails ) );
    }

    private function get_wc_cart_abandonment_recovery_template_ids() {
        if ( ! class_exists( 'YayMailAddonWcCartAbandonmentRecovery\EmailCreation' ) ) {
            return [];
        }
        $emails = \YayMailAddonWcCartAbandonmentRecovery\EmailCreation::get_instance()->get_emails();

        if ( empty( $emails ) ) {
            return [];
        }

        return array_filter(
            array_map(
                function ( $email ) {
                    return $email->get_id();
                },
                $emails
            )
        );
    }


    /**
     * Determines the source of support for a given template ID.
     *
     * This method checks if the template ID is supported by the core, an addon, or neither.
     *
     * @param string $template_id The template ID to check.
     * @return string Returns 'already_supported', 'addon_needed' if supported by an addon, 'pro_needed' if supported by pro, or 'not_supported'.
     */
    private function get_support_status( string $template_id ): string {
        // If the YayMail template data exists, it means the template is supported and ready to be edited
        if ( ! empty( $this->get_yaymail_template_data( $template_id ) ) ) {
            return 'already_supported';
        }

        /**
         * Check addons
         */
        $template_ids_from_addons = [];
        foreach ( $this->get_addon_supported_plugins() as $third_party ) {
            if ( ! empty( $third_party['template_ids'] ) && ! empty( $third_party['is_3rd_party_installed'] ) ) {
                $template_ids_from_addons = array_merge( $template_ids_from_addons, $third_party['template_ids'] );
            }
        }
        if ( in_array( $template_id, $template_ids_from_addons, true ) ) {
            return 'addon_needed';
        }

        return 'not_supported';
    }

    /**
     * Get the plugin name based on a specific template ID.
     *
     * @param array  $addons       The array of addons, each containing plugin_name and template_ids.
     * @param string $template_id The template ID to search for.
     * @return string|null        The plugin name if the template ID is found, or null if not found.
     */
    private function get_addon_info( string $template_id ): ?array {

        foreach ( $this->addon_supported_plugins as $addon ) {
            // Check if 'template_ids' exists and contains the specified template ID
            if ( isset( $addon['template_ids'] ) && in_array( $template_id, $addon['template_ids'], true ) ) {
                return $addon;
            }
        }

        return null;
    }

    /**
     * Retrieves support information for a given template.
     *
     * @param string $template_id Template id
     *
     * @return array An associative array containing:
     *               - 'support_status' (string): 'already_supported', 'addon_needed' if supported by an addon, 'pro_needed' if supported by pro, or 'not_supported'.
     *               - 'addon_info' (array|null): array (object) that has 3 fields: {plugin_name: string, template_ids: array of strings, link_upgrade: string}
     */
    public function get_support_info( string $template_id ): array {
        $support_status = $this->get_support_status( $template_id );
        $addon_info     = $this->get_addon_info( $template_id );

        return [
            'status' => $support_status,
            'addon'  => $addon_info,
        ];
    }

    public function get_yaymail_template_data( $template_id ) {
        $yaymail_emails = \yaymail_get_emails();
        return current( array_filter( $yaymail_emails, fn( $email ) => $email->get_id() === $template_id ) );
    }

    public function get_addon_supported_plugins() {
        return $this->addon_supported_plugins;
    }

    public function get_addon_supported_template_ids( string $addon_namespace ): array {
        return $this->addon_supported_plugins[ $addon_namespace ]['template_ids'] ?? [];
    }

    public function get_slug_name_supported_plugins(): array {
        return array_map(
            function( $addon ) {
                return [
                    'plugin_name' => $addon['plugin_name'] ?? '',
                    'slug_name'   => $addon['slug_name'] ?? '',
                ];
            },
            $this->addon_supported_plugins
        );
    }
}
