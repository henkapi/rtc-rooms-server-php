<?php
// START
// post offer
// get answer

// get offer
// post answer

// get answer responds
// END

// {
//     action: post, get,
//     type: offer, answer,
//     room: room_name,
//     description: .......
// }

header('Content-Type: application/json; charset=utf-8');
$max_waiting_time   = 60; // 1 minute

$path_dir_rooms     = __DIR__ . '/rooms';

$action             = $_POST['action'];
$type               = $_POST['type'];
$room               = $_POST['room'];
$description        = $_POST['description'];

$offer_prefix       = 'off';
$answer_prefix      = 'ans';
$path_offer         = $path_dir_rooms . '/' . $offer_prefix . $room;
$path_answer        = $path_dir_rooms . '/' . $answer_prefix . $room;

switch ($action) {
    case 'post':
        switch ($type) {
            case 'offer':
                // create the directory with room name
                $path_offer = tempnam($path_dir_rooms, $offer_prefix);

                // create the file offer
                file_put_contents($path_offer, $description);

                // TODO: respond with the room code
                $room = substr(basename($path_offer), len($offer_prefix));
                echo json_encode([
                    'success'   => true,
                    'data'      => [
                        'room'      => $room,
                    ],
                ]);

                break;
            case 'answer':
                if (file_exists($path_offer)) {
                    // create the file answer
                    file_put_contents($path_answer, $description);
                }

                // TODO: respond with something
                echo json_encode([
                    'success'   => true,
                ]);

                break;
            default:

                break;
        }
        break;
    case 'get':
        switch ($type) {
            case 'offer':
                // get the contents of file offer
                $offer = file_get_contents($path_offer);

                // TODO: respond with the description
                echo json_encode([
                    'success'   => true,
                    'data'      => [
                        'description'      => $offer,
                    ],
                ]);

                break;
            case 'answer':
                ini_set('max_execution_time', $max_waiting_time);

                function handle_timeout() {
                    $lastError = error_get_last();

                    if ($lastError && str_contains($lastError['message'], 'Maximum execution time')) {
                        // delete the room's files (either after timeout or after responding with description)
                        unlink($path_offer);

                        // TODO: respond with error
                        echo json_encode([
                            'success'   => false,
                        ]);
                    }
                }
                register_shutdown_function(handle_timeout);

                // check the presence of the the file answer
                while (true) {
                    if (file_exists($path_answer)) {
                        $answer = file_get_contents($path_answer);
                        unlink($path_offer);
                        unlink($path_answer);

                        // TODO: respond with the description
                        echo json_encode([
                            'success'   => true,
                            'data'      => [
                                'description'      => $answer,
                            ],
                        ]);

                    }
                    sleep(3);
                }
                break;
            default:

                break;
        }
        break;
    default:

        break;
}
