<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ChatEntity;
use App\Models\ContactEntity;
use App\Models\GroupContactEntity;
use App\Models\MessageEntity;
use App\Models\UserEntity;

/** контроллер контактов */
class ContactController extends Controller
{
    private ContactEntity $contacts;
    private GroupContactEntity $groupContacts;
    private MessageEntity $messages;
    private UserEntity $users;
    private ChatEntity $chats;

    private string $authUserEmail;
    private int $authUserId;

    public function __construct()
    {
        parent::__construct();
        $this->users = new UserEntity();
        $this->contacts = new ContactEntity();
        $this->groupContacts = new GroupContactEntity();
        $this->messages = new MessageEntity();
        $this->authUserEmail = UserController::getAuthUserEmail();
        $this->authUserId = $this->users->getIdByName($this->authUserEmail);
        $this->chats = new ChatEntity();
    }

    public function getContacts()
    {
        $userContacts = $this->contacts->getUserContacts($this->authUserId);
        for ($i = 0; $i < count($userContacts); ++$i) {
            $userContacts[$i]['photo'] = $this->getAvatarImagePath($userContacts[$i]['photo']);
        }
        echo json_encode($userContacts);
    }

    public function createGroupContact()
    {
        $discussionId = htmlspecialchars($_POST['discussionid']);
        $username = htmlspecialchars($_POST['username']);
        $userId = $this->users->getIdByName($username);

        $gcExisted = $this->groupContacts->exists($discussionId, $userId);
        if (!$gcExisted) {
            $isAdded = (int) $this->groupContacts->add($discussionId, $userId);
        } else {
            $isAdded = 1;
        }

        echo json_encode([
            'result' => $isAdded,
            'group' => 'group-'.$discussionId,
            'user' => $username,
        ]);
    }

    public function getGroupContacts()
    {
        $discussionId = htmlspecialchars($_POST['discussionid']);
        $creatorId = $this->messages->getDiscussionCreatorId($discussionId);
        echo json_encode([
            'participants' => $this->contacts->getGroupContacts($discussionId),
            'creatorName' => $this->users->getPublicUsername($creatorId),
        ]);
    }
}
