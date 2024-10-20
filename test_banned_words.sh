#!/bin/bash

BASE_URL="http://localhost:8000/api"

echo "Testing create endpoint with banned word:"
curl -X POST "$BASE_URL/blog-articles" \
     -H "Content-Type: application/json" \
     -d '{
         "authorId": 1,
         "title": "Test Article",
         "content": "This is a test article with the banned word.",
         "status": "draft"
     }'
echo -e "\n\n"

echo "Testing create endpoint without banned word:"
curl -X POST "$BASE_URL/blog-articles" \
     -H "Content-Type: application/json" \
     -d '{
         "authorId": 1,
         "title": "Test Article",
         "content": "This is a test article without banned words.",
         "status": "draft"
     }'
echo -e "\n\n"

echo "Testing update endpoint with banned word (assuming article ID 1 exists):"
curl -X PATCH "$BASE_URL/blog-articles/1" \
     -H "Content-Type: application/json" \
     -d '{
         "content": "This is an updated article with the banned word."
     }'
echo -e "\n\n"

echo "Testing update endpoint without banned word:"
curl -X PATCH "$BASE_URL/blog-articles/1" \
     -H "Content-Type: application/json" \
     -d '{
         "content": "This is an updated article without banned words."
     }'
echo -e "\n\n"