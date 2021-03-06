<?php


namespace JoisarJignesh\Bigbluebutton;

use BigBlueButton\BigBlueButton;
use BigBlueButton\Parameters\CreateMeetingParameters;
use BigBlueButton\Parameters\DeleteRecordingsParameters;
use BigBlueButton\Parameters\EndMeetingParameters;
use BigBlueButton\Parameters\GetMeetingInfoParameters;
use BigBlueButton\Parameters\GetRecordingsParameters;
use BigBlueButton\Parameters\IsMeetingRunningParameters;
use BigBlueButton\Parameters\JoinMeetingParameters;
use JoisarJignesh\Bigbluebutton\Services\initMeeting;

class Bbb
{
    use initMeeting;

    private $response;
    /**
     * @var BigBlueButton
     */
    protected $bbb;


    public function __construct(BigBlueButton $bbb)
    {
        $this->bbb = $bbb;
    }

    /*
     * return BigBlueButton\BigBlueButton
     * return BigBlueButton Class of Api class
     */
    public function make()
    {
        return $this->bbb;
    }
    /**
     *  Return a list of all meetings
     *
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        $this->response = $this->bbb->getMeetings();
        if ($this->response->success()) {
            if (count($this->response->getRawXml()->meetings->meeting) > 0) {
                $meetings = [];
                foreach ($this->response->getRawXml()->meetings->meeting as $meeting) {
                    $meetings[] = $meeting;
                }

                return collect($meetings);
            }
        }

        return collect([]);
    }


    /**
     * $meeting
     *
     * @param $meeting
     *
     * required fields
     * meetingID
     * meetingName
     *
     * @return mixed
     */
    public function create($meeting)
    {
        if (!$meeting instanceof CreateMeetingParameters) {
            $meeting = $this->initCreateMeeting($meeting);
        }

        $this->response = $this->bbb->createMeeting($meeting);
        if ($this->response->failed()) {
            return $this->response->getMessage();
        } else {
            return collect(XmlToArray($this->response->getRawXml()));
        }
    }

    /**
     * @param $meeting
     *
     * required fields:
     * meetingID
     *
     * @return bool
     */
    public function isMeetingRunning($meeting)
    {
        if (!$meeting instanceof IsMeetingRunningParameters) {
            $meeting = $this->initIsMeetingRunning($meeting);
        }

        $this->response = $this->bbb->isMeetingRunning($meeting);
        if ($this->response->success()) {
            $response = XmlToArray($this->response->getRawXml());
            if (isset($response['running']) && $response['running'] == "true") {
                return true;
            }
        }

        return false;
    }

    /**
     *  Join meeting
     *
     * @param $meeting
     * required fields
     *
     *  meetingID
     *  userName join by name
     *  password which role want to join
     *
     * @return string
     */
    public function join($meeting)
    {
        if (!$meeting instanceof JoinMeetingParameters) {
            $meeting = $this->initJoinMeeting($meeting);
        }

        if ($meeting->isRedirect()) {
            return $this->bbb->getJoinMeetingURL($meeting);
        }

        return $this->bbb->joinMeeting($meeting)->getUrl();
    }

    /**
     *  Returns information about the meeting
     *
     * @param $meeting
     * required fields
     * meetingID
     * moderatorPW must be there moderator password
     *
     * @return \Illuminate\Support\Collection
     */
    public function getMeetingInfo($meeting)
    {
        if (!$meeting instanceof GetMeetingInfoParameters) {
            $meeting = $this->initGetMeetingInfo($meeting);
        }

        $this->response = $this->bbb->getMeetingInfo($meeting);
        if ($this->response->success()) {
            return collect(XmlToArray($this->response->getRawXml()));
        }

        return collect([]);
    }

    /*
         * required fields
         * meetingID
         * meetingName
         * userName
         * attendeePW
         * moderatorPW
         */
    public function start($parameters)
    {
        return $this->initStart($parameters);
    }

    /**
     *  Close meeting
     *
     * @param  $meeting
     * required fields:
     * meetingID
     * moderatorPW close meeting must be there moderator password
     *
     * @return bool
     */
    public function close($meeting)
    {
        if (!$meeting instanceof EndMeetingParameters) {
            $meeting = $this->initCloseMeeting($meeting);
        }

        $this->response = $this->bbb->endMeeting($meeting);
        if ($this->response->success()) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param $recording
     * required fields
     * meetingID
     *
     * optional fields
     * recordID
     * state
     * @return \Illuminate\Support\Collection
     */
    public function getRecordings($recording)
    {
        if (!$recording instanceof GetRecordingsParameters) {
            $recording = $this->initGetRecordings($recording);
        }

        //2265544ef7453eed3114ae608a8501ffec72f6c0-1587228670330
        //0x8xbjIO4EPMlxhVIZVh

       // dd($recording);
        $this->response = $this->bbb->getRecordings($recording);
       // dd($this->response);
        if (count($this->response->getRawXml()->recordings->recording) > 0) {
            $recordings = [];
            foreach ($this->response->getRawXml()->recordings->recording as $r) {
                $recordings[] = $r;
            }

            return collect($recordings);
        }

        return collect([]);
    }

    /*
     * required fields
     * recordingID
     */
    public function deleteRecordings($recording)
    {
        if (!$recording instanceof DeleteRecordingsParameters) {
            $recording = $this->initDeleteRecordings($recording);
        }

        $this->response = $this->bbb->deleteRecordings($recording);
        return collect(XmlToArray($this->response->getRawXml()));
    }

}
