<x-cards.notification :notification="$notification" :link="route('tasks.show', $notification->data['id'])"
                      :image="user()->image_url"
                      :title="__('email.taskReviewApprove.rejected')" :text="$notification->data['heading']"
                      :time="$notification->created_at"/>
