UL Tools & Miscellaneous Functionalities
========================================

1. Reset the canonical URL

Instructions
------------
- Ticket UL-3937: Fix canonicals for translated content on www.ul.com.
- Implement hook funcations:
  - ul_tools_preprocess_html
  - ul_tools_entity_view_alter
  - ul_tools_page_attachments_alter
  - ul_tools_page_attachments_alter
  - ul_tools_module_implements_alter
  - _reset_header_link_canonical


========================================

Author: Gung Wang
Email : gung.wang@ul.com

Date  : Jun. 2, 2021
 - Initialize the module
 - Implement hook functions

Update: Jun 10, 2021
 - Modify ul_tools_preprocess_html
 - Do not override the canonical URL on the latam site
 - 
