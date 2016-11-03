package main

import (
	"github.com/garyburd/redigo/redis"
	"io"
	"log"
	"net/http"
	"time"
)

var (
	RedisClient *redis.Pool
	host        string
	db          int
	password    string
)

func init() {
	host = "127.0.0.1:6379"
	db = 1
	password = "123456"
	RedisClient = &redis.Pool{
		MaxIdle:     100,
		MaxActive:   100,
		IdleTimeout: 180 * time.Second,
		Dial: func() (redis.Conn, error) {
			c, err := redis.Dial("tcp", host)
			if err != nil {
				return nil, err
			}
			c.Do("AUTH", password)
			c.Do("SELECT", db)
			return c, nil
		},
	}
	c := RedisClient.Get()
	defer c.Close()
	c.Do("set","xtgxiso",[]byte("Hello xtgxiso"))
}

func DefaultServer(w http.ResponseWriter, req *http.Request) {
	c := RedisClient.Get()
	defer c.Close()
	s, err := redis.String(c.Do("get", "xtgxiso"))
	if err != nil {
		log.Println("error:", err)
	}
	io.WriteString(w, s)
}

func main() {
	http.HandleFunc("/", DefaultServer)
	err := http.ListenAndServe(":1215", nil)
	if err != nil {
		log.Fatal("ListenAndServe: ", err)
	}
}
