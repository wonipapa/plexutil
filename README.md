# 플렉스 에이전트용 유틸티리
## PHP7 버전 용입니다
php 7이 아니라면 아래 부분을  
$ProgramId = $argv[1] ?? exit();  
$start_num = $argv[2] ?? 1;  
이와 같이 바꿔주세요.  
$ProgramId = isset($argv[1]) ? $argv[1] :  exit();  
$start_num = isset($argv[2]) ? $argv[2] : 1;  
## daumjson
다음에서 제공하는 TV 프로그램 정보를 json 파일로 만들어 주는 유틸리티-다음 사이트 변경으로 작동안됨

### 사용법
먼저 리눅스에 접속한 후 실행 권한을 준다.  
chmod +x daumjson.php   
json 파일로 만들고자 하는 tv 프로그램을 http://www.daum.net 에서 검색후 검색결과 url의 https://search.daum.net/search?w=tv&q=검색어&irk=숫자&irt=tv-program&DA=TVP 의 숫자부분을 이용한다.  
./daumjson.php 숫자  
옵션사항으로 회차 번호를 부여할 수 있다. 회차 번호 부여시 에피소드 번호가 부여한 회차번호부터 시작한다.  
./daumjson.php 숫자  10

## pooqjson
Pooq에서 제공하는 TV 프로그램 정보를 json 파일로 만들어 주는 유틸리티

### 사용법
먼저 리눅스에 접속한 후 실행 권한을 준다.  
chmod +x pooqjson.php   
json 파일로 만들고자 하는 tv 프로그램을 http://www.pooq.co.kr 에서 검색후 검색결과 url의 http://www.pooq.co.kr/player/vod.html?programid=문자 의 문자 부분을 이용한다.  
./pooqjson.php 문자  
옵션사항으로 회차 번호를 부여할 수 있다. 회차 번호 부여시 에피소드 번호가 부여한 회차번호부터 시작한다.  
./pooqjson.php 문자  10


결과 파일은 TV 프로그램명.json 파일로 저장되며, 편집기로 필요한 추가 사항등을 편집할 수 있다.

## plextojson
주 용도는 plex 라이브러리의 메타정보를 외부 환경의 변화에 상관없이 보관하여 라이브러리 복구, 이동시 편의를 위한 것이다  
보조 용도로는 관리편의상 라이브러리 중 시청빈도가 적은 영상들을 따로 관리하여 필요시 복구하는 방법으로 라이브러리의 크기를 적정한 크기로 관리하는 것이다.  

plex에서 관리되는 영화, TV의 라이브러리를 json 파일로 저장한다  
영화는 영화명.json, 영화명 poster.jpg, 영화명 art.jpg 로 저장된다.  

TV는 TV 프로그램명.json, TV 프로그램명 시즌 01.json, TV 프로그램명 시즌 02.json 형식으로 저장된다  
TV 프로그래명.json 에는 전반적인 정보가, TV 프로그램명 시즌 01.json에는 에피소드 정보와 제작자 등의 정보가 저장된다.  

처음 사용할 때는 plextojson.php의 내용중 아래 부분을 자신에 맞게 수정해야 한다.  

$PLEX_DIR = '/var/lib/plexmediaserver/Library/Application Support/Plex Media Server';  
$PLEX_META_DIR = $PLEX_DIR."/"."Metadata";  
$PLEX_DB_DIR = $PLEX_DIR."/"."Plug-in Support/Databases";  
$DB = $PLEX_DB_DIR."/"."com.plexapp.plugins.library.db";  

### 사용법
./plextojson 번호  

번호는 plex 주소에서 확인가능하다. 아래 예에서는 60813이 번호다  
http://****library%2Fmetadata%2F60813

## toDo
만들어진 json 파일을 활용하는 agent는 추후 공개 예정
