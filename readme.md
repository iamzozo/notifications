#Simple notification framework

You can create, get and delete notifications for users and posts.

##Usage

###get_notifications($id, [target])
ex.: ``get_notifications(2, 'post')``

You will get user IDs who get notifications for post with ID 2

``get_notifications(13, 'user')``
You will get post IDs for user with ID 13

###delete_notifications($id, [target])

###create_notification($user_id, $post_id)