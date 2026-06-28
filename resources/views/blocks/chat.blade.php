@php
    $user = App\Models\User::query()
    ->with(['chatroom', 'group', 'settings'])
    ->find(auth()->id());
@endphp

<section
    id="chatbody"
    class="panelV2 chatbox"
    x-data="chatbox(@js($user))"
    :class="state.ui.fullscreen && 'chatbox--fullscreen'"
>
    <div class="loading__spinner" x-show="state.ui.loading">
        <div class="spinner__dots">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
        <div class="spinner__text">Chatbox loading</div>
    </div>

    <div x-show="!state.ui.loading">
        <header class="panel__header" id="chatbox_header">
            <h2 class="panel__heading">
                <i class="fas fa-comment-dots"></i>
                Chatbox
            </h2>
            <div class="panel__actions">
                <div class="panel__action">
                    <button
                        class="form__button form__button--text"
                        @click.prevent="changeBot(state.chat.bot.id)"
                    >
                        <i class="fa fa-robot"></i>
                        <span x-text="state.chat.bot?.name"></span>
                    </button>
                </div>
                <div class="panel__action" x-show="state.chat.conversation?.room !== null">
                    <button class="form__button form__button--text" @click.prevent="toggleUserList">
                        <i class="fa fa-users"></i>
                        Users:
                        <span x-text="users.size"></span>
                    </button>
                </div>
                <div class="panel__action">
                    <button
                        class="form__button form__standard-icon-button form__standard-icon-button--skinny"
                        @click.prevent="changeAudible(state.chat.conversation?.id)"
                        :style="'color: ' + (state.chat.conversation?.audible ? 'rgb(0,102,0)' : 'rgb(204,0,0)')"
                    >
                        <i
                            :class="state.chat.conversation?.audible ? 'fa fa-bell' : 'fa fa-bell-slash'"
                        ></i>
                    </button>
                </div>
                <div class="panel__action">
                    <button
                        class="form__button form__standard-icon-button form__standard-icon-button--skinny"
                        title="Toggle typing notifications"
                        @click.prevent="changeWhispers()"
                        :style="'color: ' + (state.chat.showWhispers ? 'rgb(0,102,0)' : 'rgb(204,0,0)')"
                    >
                        <i
                            :class="state.chat.showWhispers ? 'fas fa-keyboard' : 'fa fa-keyboard'"
                        ></i>
                    </button>
                </div>
                <div class="panel__action">
                    <div class="form__group">
                        <select
                            id="currentChatroom"
                            class="form__select"
                            x-model.number="state.chat.room"
                        >
                            <template x-for="chatroom in chatrooms" :key="chatroom.id">
                                <option :value="chatroom.id" x-text="chatroom.name"></option>
                            </template>
                        </select>
                        <label class="form__label form__label--floating" for="currentChatroom">
                            Room
                        </label>
                    </div>
                </div>
                <div class="panel__action">
                    <div class="form__group">
                        <select
                            id="currentChatstatus"
                            class="form__select"
                            x-model.number="auth.chat_status_id"
                        >
                            <template x-for="chatstatus in statuses" :key="chatstatus.id">
                                <option
                                    :value="chatstatus.id"
                                    :selected="chatstatus.id === auth.chat_status_id"
                                    x-text="chatstatus.name"
                                ></option>
                            </template>
                        </select>
                        <label class="form__label form__label--floating" for="currentChatstatus">
                            Status
                        </label>
                    </div>
                </div>
                <div class="panel__action">
                    <button
                        id="panel-fullscreen"
                        class="form__button form__standard-icon-button"
                        title="Toggle fullscreen"
                        @click.prevent="changeFullscreen()"
                    >
                        <i :class="state.ui.fullscreen ? 'fas fa-compress' : 'fas fa-expand'"></i>
                    </button>
                </div>
            </div>
        </header>
        <menu id="chatbox_tabs" class="panel__tabs" role="tablist">
            <template x-for="conversation in conversations" :key="conversation.id">
                <li
                    class="panel__tab chatbox__tab"
                    :class="state.chat.conversation?.id === conversation.id && 'panel__tab--active'"
                    role="tab"
                    @click.prevent="changeConversation(conversation.id)"
                >
                    <i
                        class="fa fa-comment"
                        :class="checkPings(conversation) ? 'fa-beat text-success' : 'text-danger'"
                    ></i>
                    <span
                        x-text="conversation.room?.name || conversation.target?.username || conversation.bot?.name || ''"
                    ></span>
                    <button
                        x-show="state.chat.conversation?.id === conversation.id"
                        class="chatbox__tab-delete-button"
                        @click.prevent="leaveConversation(conversation.id)"
                    >
                        <i class="fa fa-times chatbox__tab-delete-icon"></i>
                    </button>
                </li>
            </template>
        </menu>
        <div class="chatbox__chatroom">
            <template x-if="state.chat.conversation !== null">
                <div class="chatroom__messages--wrapper" x-ref="messagesWrapper">
                    <ul class="chatroom__messages">
                        <template x-for="message in [...messages.values()]" :key="message.id">
                            <li>
                                <article class="chatbox-message">
                                    <header class="chatbox-message__header">
                                        <address
                                            class="chatbox-message__address user-tag"
                                            :style="(message.user?.is_donor ? 'background-image: url(/img/sparkels.gif);' : (message.user?.group?.effect ? 'background-image:' + message.user.group.effect + ';' : ''))"
                                        >
                                            <a
                                                class="user-tag__link"
                                                :class="message.user?.group?.icon"
                                                :href="message.user?.username ? '/users/' + message.user.username : ''"
                                                :style="message.user?.group?.color ? 'color:' + message.user.group.color : ''"
                                                :title="message.user?.group?.name"
                                            >
                                                <span
                                                    x-show="message.user && message.user.id > 1"
                                                    style="padding-right: 5px"
                                                    x-text="message.user?.username || 'Unknown'"
                                                ></span>
                                                <span
                                                    x-show="message.bot && message.bot.id >= 1 && (! message.user || message.user.id < 2)"
                                                    x-text="message.bot?.name || 'Unknown'"
                                                ></span>
                                                <template x-if="message.user?.icon">
                                                    <i>
                                                        <img
                                                            :style="'max-height: 16px; vertical-align: text-bottom;'"
                                                            title="Custom user icon"
                                                            :src="'/authenticated-images/user-icons/' + message.user.username"
                                                            loading="lazy"
                                                        />
                                                    </i>
                                                </template>
                                                <i
                                                    x-show="message.user?.is_lifetime == 1"
                                                    class="fal fa-star"
                                                    id="lifeline"
                                                    title="Lifetime donor"
                                                ></i>
                                                <i
                                                    x-show="message.user?.is_donor == 1 && message.user?.is_lifetime == 0"
                                                    class="fal fa-star text-gold"
                                                    title="Donor"
                                                ></i>
                                            </a>
                                        </address>
                                        <div
                                            x-show="message.bot && message.bot.id >= 1 && (! message.user || message.user.id < 2)"
                                            class="bbcode-rendered bot-message"
                                            style="
                                                font-style: italic;
                                                white-space: nowrap;
                                                display: inline;
                                            "
                                            x-html="message.message"
                                        ></div>
                                        <time
                                            x-show="message.bot && message.bot.id >= 1 && (! message.user || message.user.id < 2)"
                                            style="
                                                margin-left: 10px;
                                                white-space: nowrap;
                                                display: inline;
                                            "
                                            class="chatbox-message__time"
                                            :datetime="message.created_at"
                                            :title="message.created_at"
                                            x-text="formatTime(message.created_at)"
                                        ></time>
                                        <time
                                            x-show="! (message.bot && message.bot.id >= 1 && (! message.user || message.user.id < 2))"
                                            class="chatbox-message__time"
                                            :datetime="message.created_at"
                                            :title="message.created_at"
                                            x-text="formatTime(message.created_at)"
                                        ></time>
                                    </header>
                                    <aside class="chatbox-message__aside">
                                        <figure class="chatbox-message__figure">
                                            <i
                                                class="fa fa-bell"
                                                title="System notification"
                                                x-show="message.bot && message.bot.id >= 1 && (! message.user || message.user.id < 2)"
                                            ></i>
                                            <a
                                                x-show="message.user && message.user.id != 1"
                                                :href="'/users/' + message.user.username"
                                                class="chatbox-message__avatar-link"
                                            >
                                                <img
                                                    x-show="message.user && message.user.id != 1"
                                                    class="chatbox-message__avatar"
                                                    :src="message.user?.image ? '/authenticated-images/user-avatars/' + message.user.username : '/img/profile.png'"
                                                    :style="'border: 2px solid ' + (message.user?.chat_status?.color || '#ccc')"
                                                    :title="message.user?.chat_status?.name"
                                                    loading="lazy"
                                                />
                                            </a>
                                        </figure>
                                    </aside>
                                    <section
                                        @class([
                                            'bbcode-rendered',
                                            'chatbox-message__content',
                                            'bbcode-rendered__censor' => $user->settings->censor,
                                        ])
                                        x-show="! (message.bot && message.bot.id >= 1 && (! message.user || message.user.id < 2))"
                                        x-html="message.message"
                                    ></section>
                                    <!-- Move menu back to original position after timestamp -->
                                    <menu
                                        class="chatbox-message__menu"
                                        x-show="message.canMod === true || message.canMod === 1"
                                    >
                                        <li class="chatbox-message__menu-item">
                                            <button
                                                class="chatbox-message__delete-button"
                                                title="Delete message"
                                                @click.prevent="deleteMessage(message.id)"
                                                style="
                                                    cursor: pointer;
                                                    padding: 0;
                                                    margin-left: 8px;
                                                "
                                            >
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </li>
                                    </menu>
                                </article>
                            </li>
                        </template>
                        <li x-show="messages.size === 0">
                            There is no chat history here. Send a message!
                        </li>
                    </ul>
                </div>
            </template>
            <section
                class="chatroom__users"
                x-show="state.chat.showUserList && state.chat.target < 1 && state.chat.bot < 1"
            >
                <h2 class="chatroom-users__heading">Users</h2>
                <ul class="chatroom-users__list">
                    <template x-for="user in [...users.values()]" :key="user.id">
                        <li class="chatroom-users__list-item">
                            <span class="chatroom-users__user user-tag">
                                <a
                                    class="chatroom-users__user-link user-tag__link"
                                    :href="'/users/' + user.username"
                                >
                                    <span x-text="user.username"></span>
                                </a>
                            </span>
                            <menu class="chatroom-users__buttons" x-show="auth.id !== user.id">
                                <li>
                                    <button
                                        class="chatroom-users__button"
                                        title="Gift user bon"
                                        @click.prevent="forceGift(user.username)"
                                    >
                                        <i class="fas fa-gift"></i>
                                    </button>
                                </li>
                                <li>
                                    <button
                                        class="chatroom-users__button"
                                        title="Send chat PM"
                                        @click.prevent="forceMessage(user.username)"
                                    >
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                </li>
                            </menu>
                        </li>
                    </template>
                </ul>
            </section>
            <section class="chatroom__whispers" x-show="state.chat.showWhispers">
                <span
                    x-show="state.chat.target < 1 && state.chat.bot < 1 && activePeer && activePeer.size > 0"
                    x-text="
                        activePeer.size > 3
                            ? 'Several people are typing...'
                            : activePeer.size === 1
                              ? [...activePeer.keys()][0] + ' is typing...'
                              : [...activePeer.keys()].slice(0, -1).join(', ') +
                                ' and ' +
                                [...activePeer.keys()][activePeer.size - 1] +
                                ' are typing...'
                    "
                ></span>
            </section>
            <form
                class="form chatroom__new-message"
                @submit.prevent="createMessage($refs.message.value)"
            >
                <p class="form__group">
                    <textarea
                        id="chatbox__messages-create"
                        class="form__textarea"
                        name="message"
                        placeholder=" "
                        x-ref="message"
                        @keydown.enter="!$event.shiftKey && ($event.preventDefault(), createMessage($refs.message.value), $refs.message.value = '')"
                        @keyup="isTyping(auth)"
                    ></textarea>
                    <label class="form__label form__label--floating" for="chatbox__messages-create">
                        Write your message...
                    </label>
                </p>
            </form>
        </div>
    </div>
</section>
