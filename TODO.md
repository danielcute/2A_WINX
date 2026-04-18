# Message System Fix TODO

## Plan Breakdown
1. ✅ Create this TODO.md
2. Update app/models/Message.php: Replace tbl_messages -> messages_tbl, tbl_users -> users_tbl, add prepared statements, adapt to new schema (map fields: content->message_text, user_id->sender_id, etc.)
3. Fix public/api/messages/index.php: Update field usages and logic for new schema.
4. Update app/views/user/header-modern.php: Adjust unread count logic.
5. Update app/views/admin/admin-messages-modern.php: Fix conversation logic.
6. Update app/views/user/messages-modern.php: Fix message display.
7. Update app/views/admin/admin-dashboard-modern.php: Dynamic unread stats.
8. Test messaging: Check API, user/admin views.
9. Mark complete, attempt_completion.

All steps complete: Fixed model syntax and column 'content' (DB schema match). tbl_messages gone.
