<?php

/**
 * Class FileUpload
 * Creator Newvid ( thtjwls@newvid.co.kr )
 *
 * 시작하기 :
 * FileUpload 인스턴트 객체 생성 후 do_upload() 메서드를 실행하면 파일업로드가 최종적으로 끝난다.
 * 업로드가 생성되면 output(array) 에 config 정보와 file 정보가 출력된다.
 * 인스턴트 객체의 (array)['upload'] 값이 true 면 전송완료..
 *
 * @config = array()
 *
 * @OverrideProperty
 *
 * @comment 파일의 경로를 설정한다.
 * $config['path'] : default - server_root/
 *
 * @comment 업로드 파일의 인덱스 명을 설정한다.
 * $config['index'] : default - date('YmdHis')
 *
 * @comment 업로드 가능 한 파일을 배정한다.
 * $config['access'] : default - array('image/png','image/jpg','image/gif','image/jpeg')
 *
 * @warning 이 외의 config 파일을 Overriding 하게 되면 예상하지 못한 결과 값이 나올 수 있습니다.
 * @warning 파일 경로 끝 또는 폴더이름에 '/' 를 넣지마세요. 컨트롤러가 대신 넣어줄수 있습니다. 넣게되면 경로가 달라집니다.
 */

class FileUpload
{
    //업로드 초기설정
    public $config;

    //파일
    public $file;

    //파일 rename
    public $renameFile;

    //파일 풀네임(경로 포함)
    public $filename;

    //access file type
    public $accept;

    //최종 결과물
    public $output;

    public function __construct( $file )
    {
        //파일을 멤버변수에 추가
        $this->file = $file;

        //한글일때 유니코드 변환
        $this->file['name'] = urlencode($this->file['name']);

        //파일경로 기본설정
        $this->config['path'] = $_SERVER['DOCUMENT_ROOT'];
        
        //파일이 저장 될 폴더 생성
        $this->config['folder'] = '';

        //풀(full) 경로
        $this->config['fullpath'] = '';

        //파일명 앞에 인덱스 추가
        $this->config['index'] = date('YmdHis') . '_';

        //파일디렉토리 퍼미션
        $this->config['permission'] = 0777;

        //액세스 가능한 파일정보
        $this->config['access'] = array(
            'image/png','image/jpg','image/gif','image/jpeg'
        );


    }

    private function _folder()
    {
        $this->config['folder'] = str_replace( '/','',$this->config['folder'] );
        $this->config['fullpath'] = $this->config['path'] . '/' . $this->config['folder'] . '/';

        if ( ! is_dir( $this->config['path'] . '/' . $this->config['folder'] ) )
        {
            $result = mkdir( $this->config['fullpath'] , $this->config['permission'] );

            if ( $result == false ) {
                print_r('Directory Create fail : ' . $result);
                exit;
            } else {
                return true;
            }
        } else {

            return true;
        }

    }

    //리네임 메서드
    private function _rename()
    {

        self::_folder();

        $this->renameFile = $this->config['index'] . $this->file['name'];

        $this->filename = $this->config['path'] . '/' . $this->config['folder'] . '/' . $this->renameFile;
    }

    /**
     * 파일 업로드 실행 메서드
     * 이 메서드를 실행하면 실제 파일을 업로드 할수있다.
     */
    public function do_upload()
    {
        self::_rename();

        //확장자 확인
        if ( ! in_array( $this->file['type'],$this->config['access'] ) )
        {

            echo '파일 타입이 이상합니다.';
            echo '액세스 가능 한 타입은 다음과 같습니다.';

            foreach ( $this->config['type'] as $value )
            {
                echo $value . '<br>';
            }

            exit;
        }

        //error 타입 확인
        if ( $this->file['error'] != UPLOAD_ERR_OK )
        {
            switch ( $this->file['error'] )
            {
                case UPLOAD_ERR_OK : ;
                case UPLOAD_ERR_INI_SIZE :
                    echo '서버의 설정 값보다 파일 사이즈가 큽니다.<br>FILE_SIZE : ' . $this->file['size'];
                    break;
                case UPLOAD_ERR_FORM_SIZE :
                    echo '파일이 HTML 의 폼에서 지정한 MAX_FILE_SIZE 보다 큽니다.';
                    break;
                case UPLOAD_ERR_PARTIAL :
                    echo '파일이 일부분만 전송되엇습니다.';
                    break;

                /**
                 * 파일 필드가 존재하나, 실제 파일이 존재하지 않을경우
                 */
                case UPLOAD_ERR_NO_FILE :
                    break;
                case UPLOAD_ERR_NO_TMP_DIR :
                    echo '임시폴더가 없습니다.';
                    break;
                case UPLOAD_ERR_CANT_WRITE :
                    echo '디스크에 파일쓰기를 실패했습니다.';
                    break;
                case UPLOAD_ERR_EXTENSION :
                    echo '확장에 의해 파일 업로드가 중지되었습니다.';
                    break;
                default : echo '파일 업로드에 실패하였습니다.'; break;

            }
            exit;
        }


        //파일이동
        move_uploaded_file( $this->file['tmp_name'] , $this->filename );

        $this->output['config'] = $this->config;
        $this->output['file']   = $this->file;
        $this->output['fullname'] = $this->filename;
        $this->output['renameFile'] = $this->renameFile;
        $this->output['upload'] = true;

        return $this->output;
    }

}