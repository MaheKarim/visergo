<div class="messaging p-3">
    <div class="inbox_msg">
        <ul class="msg-list d-flex flex-column">
            @foreach ($messages as $message)
                <li class="msg-list__item">
                    <div class="{{ $message->user_id > 0 ? 'msg-receive' : 'msg-send' }}">
                        <div class="msg-receive__content">
                            <p class="msg-receive__text mb-0">
                                {{ $message->message }}
                            </p>
                        </div>
                        <ul class="msg-receive__history">
                            <li class="msg-receive__history-item">
                                {{ showDateTime($message->created_at, 'i:s A') }}
                            </li>
                            <li class="msg-receive__history-item">
                                {{ diffForHumans($message->created_at) }}
                            </li>
                        </ul>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
