#!/usr/bin/env python
# coding: utf-8

# In[1]:


import http.server
import socketserver


# In[ ]:


class SimpleHTTPRequestHandler(http.server.BaseHTTPRequestHandler):

    def do_GET(self):
        # Send the HTTP response
        self.send_response(200)
        self.send_header('Content-type', 'text/html')
        self.end_headers()

        # Send the HTML content
        self.wfile.write(b'<!DOCTYPE html>\n<html>\n<head>\n<title>Chatbot Web Server</title>\n</head>\n<body>\n<h1>Chatbot web server</h1>\n</body>\n</html>')


# In[ ]:


PORT = 8000  # Change this if you want to use a different port

httpd = socketserver.TCPServer(('', PORT), SimpleHTTPRequestHandler)


# In[ ]:


print(f'Serving on port {PORT}')
httpd.serve_forever()

