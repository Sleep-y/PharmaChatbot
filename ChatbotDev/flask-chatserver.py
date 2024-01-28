#!/usr/bin/env python
# coding: utf-8

# In[9]:


# Importing flask module in the project is mandatory
# An object of Flask class is our WSGI application.
import json
import contractions
import numpy as np
import tensorflow as tf
import keras
import pymysql
from flask import Flask, render_template, request, jsonify
from flask_cors import CORS
from nltk import pos_tag, word_tokenize
from nltk.corpus import stopwords
from keras_preprocessing.text import tokenizer_from_json
from keras.models import Model, Sequential #This works apparently and I have no idea why
from tensorflow.keras.preprocessing.text import Tokenizer
from tensorflow.keras.preprocessing.sequence import pad_sequences

with open('tokenizer.json') as f:
    data = json.load(f)
    tokenizer = tokenizer_from_json(data)
    
#Database connection
pharmaDB = pymysql.connect(host="localhost", user="root", password="", database="pharma")
pharmyDB = pymysql.connect(host="localhost", user="root", password="", database="pharmy")

#Cursors
pharmaCursor = pharmaDB.cursor()
pharmyCursor = pharmyDB.cursor()
#pharmaCursor = pharmaDB.cursor(pymysql.cursors.DictCursor)
#pharmyCursor = pharmyDB.cursor(pymysql.cursors.DictCursor)
    
#Parameters of Tokenizer
vocab_size = 1000
max_length = 50
trunc_type='post'
padding_type='post'
oov_tok = "<OOV>"

stop_words = set(stopwords.words('english'))

intent_recognition_model = keras.models.load_model('intent_recognition_model.keras')
probability_model = Sequential([intent_recognition_model, tf.keras.layers.Softmax()])

def getTokens(user_prompt): #Accepts string
    tokens = user_prompt
    tokens = contractions.fix(user_prompt)
    #print(f"User Prompt: {user_prompt}")
    tokens = tokenizer.texts_to_sequences([tokens])
    tokens = pad_sequences(tokens, maxlen=max_length, padding=padding_type) #Scale the input for the model
    #print(f"Tokens: {tokens}")
    return tokens
    

def getIntent(prompt): #Accepts string
    tokens = getTokens(prompt)
    probabilities = probability_model.predict(tokens)
    #print(f"Probabilities: {probabilities}")
    predicted_label = np.argmax(probabilities)
    #print(f"Tokens: {tokens}")
    return predicted_label

token_ban_list = ["available"] #Put words that trigger false key terms here
    #We must identify nouns for keyword extraction
def getKeywords(user_prompt): #Obtain nouns from tokens using POS Tagger - Returns a list
    tokens = word_tokenize(user_prompt)
    tags = pos_tag(tokens)
    extracted_keywords = []
    possible_keyterm = ""
    for index in range(len(tags)): #Try to get key terms based on POS tags of words
        if(tags[index][1] in ["JJ", "VBN", "NN", "NNS", "NNP", "NNPS"] and tags[index][0] not in token_ban_list):
            possible_keyterm = possible_keyterm + f"{tags[index][0]} "
        else:
            extracted_keywords.append(possible_keyterm)
            possible_keyterm = ""
    extracted_keywords.append(possible_keyterm)
    extracted_keywords = [word.strip() for word in extracted_keywords if word != ""]
    #print(tokens)
    #print(tags)
    print(f"extracted_keywords: {extracted_keywords}")
    return extracted_keywords
    

#0: Contact Human Agent - Ask for live human agent
#1: Medicine Suggestion Query - Ask for suggestion of OTC medicines based from symptoms or generic drug name (ex. do you have anything for flu?; do you have vitamin D supplements?). 
#2: Medicine Dosage Query - Ask for dosage directions
#3: Medicine Availability Query - 

def getDosage(keywords):
    result = ""
    app.logger.info(f"getDosage()")
    for i in range(len(keywords)):
        app.logger.info(f"keyword: {keywords[i]}")
        query = f"SELECT LOWER(generic_name) FROM medicine_profile WHERE LOWER(medicine_name) = LOWER(%s) or LOWER(generic_name) = LOWER(%s)"
        app.logger.info(f"query: {query}")
        pharmaCursor.execute(query, (keywords[i], keywords[i]))
        if(pharmaCursor.rowcount == 0):
            app.logger.info(f"{keywords[i]}: Abandoning...")
            continue
        data = pharmaCursor.fetchone()
        app.logger.info(f"data: {data}")
        
        query = f"SELECT dosage FROM generic_medicine WHERE LOWER(genericName) = LOWER(%s)"
        app.logger.info(f"query: {query}")
        pharmyCursor.execute(query, (data))
        if(pharmyCursor.rowcount == 0):
            app.logger.info(f"data: Abandoning...")
            continue
        data = pharmyCursor.fetchone()
        data = data[0]
        app.logger.info(f"data: {data}")
        result = result + f"Dosage for {keywords[i]}: {data}\n"
    return result

def getAvailability(keywords):
    result = ""
    app.logger.info(f"getAvailability()")
    for i in range(len(keywords)):
        app.logger.info(f"keyword: {keywords[i]}")
        query = f"SELECT inventory_general.id, quantity FROM inventory_general INNER JOIN medicine_profile on inventory_general.medicine_id = medicine_profile.id WHERE LOWER(medicine_name) = LOWER(%s)"
        app.logger.info(f"query: {query}")
        pharmaCursor.execute(query, (keywords[i]))
        if(pharmaCursor.rowcount == 0):
            app.logger.info(f"keyword: Abandoning...")
            continue
        data = pharmaCursor.fetchall()
        app.logger.info(f"data: {data}")
        sum = 0
        id_link = []
        for id, num in data:
            id_link = id
            sum += num
        app.logger.info(f"data: {data}")
        result = result + f"Availability of {keywords[i]}: {sum} in stock\n"
        result = result + f"Store link for {keywords[i]}: localhost/pharma/shop-single?id={id_link}\n"
    #result = result + getMedLink(keywords)
    return result

'''
#Looks like they used inventory_general.id and passed that in the store-single link...?
def getMedLink(keywords):
    result = ""
    app.logger.info(f"getMedLink()")
    for i in range(len(keywords)):
        app.logger.info(f"keyword: {keywords[i]}")
        query = f"SELECT id FROM inventory_general INNER JOIN medicine_profile on inventory_general.medicine_id = medicine_profile.id WHERE LOWER(medicine_name) = LOWER(%s)"
        app.logger.info(f"query: {query}")
        pharmaCursor.execute(query, (keywords[i]))
        if(pharmaCursor.rowcount == 0):
            app.logger.info(f"keyword: Abandoning...")
            continue
        data = pharmaCursor.fetchone()
        app.logger.info(f"data: {data}")
        result = result + f"Store link for {keywords[i]}: [Store Link](https://localhost/pharma/shop-single?id={data})\n"
    return result
'''

def GenerateBotResponse(prompt):
    intent = getIntent(prompt)
    keywords = getKeywords(prompt)
    chatbot_response=""
    if (intent == 0): #???
        chatbot_response = "We will get a human agent to talk with you as soon as possible."
    elif (intent == 1): 
        chatbot_response = "Here is the medicine you asked for."
    elif (intent == 2): #Done
        chatbot_response = getDosage(keywords)
    elif (intent == 3): #Done hopefully
        chatbot_response = getAvailability(keywords)
    else:
        return "I'm sorry, I don't understand. Can you please rephrase what you said so I can understand better?"
    
    if(chatbot_response == ""):
        return "An error has occured."
    app.logger.info(f"chatbot_response: {chatbot_response}")
    if(chatbot_response == ""):
        message = {"answer" : "I'm sorry, I don't understand. Can you please rephrase what you said so I can understand better?"}
    else:
        message = {"answer": chatbot_response}
    return message

app = Flask(__name__, template_folder='chat_templates')
#cors = CORS(app, origins=['http://localhost:5000', 'http://localhost:300'])
cors = CORS(app)

@app.route("/")
def index():
    app.logger.warning("Testing...")
    app.logger.warning(pharmaDB)
    return render_template("chat.html")
    
@app.post("/chat")
def chat():
    prompt = request.get_json().get("message")
    message = GenerateBotResponse(prompt)
    return jsonify(message)
    #return jsonify( {"answer": "Connection is ok."})
     
# main driver function
# Should be at the very bottom
if __name__ == '__main__':
 
    # run() method of Flask class runs the application 
    # on the local development server.
    app.run(debug=True)