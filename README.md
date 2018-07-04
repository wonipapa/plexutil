# 플렉스 에이전트용 유틸티리
## daumjson
다음에서 제공하는 TV 프로그램 정보를 json 파일로 만들어 주는 유틸리티

### 사용법
먼저 리눅스에 접속한 후 실행 권한을 준다.  
chmod +x daumjson.php   
json 파일로 만들고자 하는 tv 프로그램을 http://movie.daum.net 에서 검색후 검색결과 url의 http://movie.daum.net/tv/main?tvProgramId=숫자 의 숫자부분을 이용한다.  
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

