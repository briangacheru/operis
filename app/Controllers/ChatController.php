<?php
declare(strict_types=1);

class ChatController extends Controller
{
    private ChatRepository  $chat;
    private UserRepository  $users;
    private AdminRepository $admins;

    public function __construct()
    {
        parent::__construct();
        $this->chat   = new ChatRepository();
        $this->users  = new UserRepository();
        $this->admins = new AdminRepository();
    }

    // GET /chat
    public function index(Request $req, Response $res): never
    {
        $email = $this->auth();
        $me    = $this->resolveUser($email);

        $admins        = $this->admins->getAll();
        $conversations = [];
        foreach ($admins as $admin) {
            $latest = $this->chat->getLatestBetween($me['id'], (int) $admin['id']);
            $conversations[] = array_merge($admin, ['latest' => $latest]);
        }

        $this->view('chat.index', [
            'me'            => $me,
            'conversations' => $conversations,
        ]);
    }

    // GET /chat/{adminId}
    public function conversation(Request $req, Response $res): never
    {
        $email   = $this->auth();
        $me      = $this->resolveUser($email);
        $adminId = (int) $req->param('adminId');
        $admin   = $this->admins->findById($adminId);

        if (!$admin) {
            $this->abort(404, 'Admin not found.');
        }

        $messages = $this->chat->getConversation($me['id'], $adminId);
        $this->chat->markRead($adminId, $me['id']);

        $this->view('chat.conversation', [
            'me'       => $me,
            'admin'    => $admin,
            'messages' => $messages,
        ]);
    }

    // GET /chat/poll?since=
    public function poll(Request $req, Response $res): never
    {
        $email = $this->auth();
        $me    = $this->resolveUser($email);
        $since = $req->query('since', '0000-00-00 00:00:00');
        $msgs  = $this->chat->getNewSince($me['id'], 'writer', $since);
        $this->json(['data' => $msgs]);
    }

    // POST /chat/send
    public function send(Request $req, Response $res): never
    {
        $email      = $this->auth();
        $me         = $this->resolveUser($email);
        $receiverId = Validator::sanitizeInt($req->input('receiver_id'));
        $message    = trim($req->input('message', ''));

        if (!$receiverId || !$message) {
            $this->json(['error' => 'receiver_id and message required'], 422);
        }

        $id = $this->chat->send([
            'sender_id'     => $me['id'],
            'sender_type'   => 'writer',
            'receiver_id'   => $receiverId,
            'receiver_type' => 'admin',
            'message'       => $message,
            'file_url'      => $req->input('file_url'),
        ]);

        $this->json(['id' => $id, 'timestamp' => date('Y-m-d H:i:s')], 201);
    }

    // POST /chat/read
    public function markRead(Request $req, Response $res): never
    {
        $email    = $this->auth();
        $me       = $this->resolveUser($email);
        $senderId = Validator::sanitizeInt($req->input('sender_id'));

        if ($senderId) {
            $this->chat->markRead($senderId, $me['id']);
        }

        $this->json(['message' => 'Marked as read.']);
    }

    private function resolveUser(string $email): array
    {
        $user = $this->users->findByEmail($email) ?? $this->admins->findByEmail($email);
        if (!$user) {
            $this->abort(401, 'User not found.');
        }
        return $user;
    }
}
