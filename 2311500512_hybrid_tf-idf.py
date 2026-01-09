import math

from nltk.corpus import stopwords
from nltk.tokenize import word_tokenize, sent_tokenize
from collections import defaultdict

text = """
Regresi linear adalah salah satu algoritma dasar dalam machine learning yang bekerja dengan memodelkan hubungan linear antara variabel input (independen) dan variabel output (dependen). Model ini berusaha menemukan garis lurus terbaik yang dapat meminimalkan jarak antara prediksi model dengan data aktual, yang dikenal sebagai "line of best fit". 
Cara kerja regresi linear dimulai dengan menentukan persamaan garis lurus y = mx + b, di mana y adalah variabel target yang ingin diprediksi, x adalah variabel input, m adalah slope (kemiringan garis), dan b adalah intercept (titik potong dengan sumbu y). Model akan mencari nilai m dan b yang optimal menggunakan metode yang disebut Ordinary Least Squares (OLS), yang bekerja dengan meminimalkan jumlah kuadrat dari selisih antara nilai prediksi dan nilai aktual.
Dalam prosesnya, model regresi linear menggunakan gradient descent, sebuah algoritma optimasi yang secara iteratif menyesuaikan nilai parameter (m dan b) untuk mengurangi error prediksi. Pada setiap iterasi, model menghitung error prediksi, kemudian mengupdate parameter dengan menggerakkannya ke arah yang menurunkan error. Proses ini berlanjut hingga model mencapai konvergensi, yaitu ketika perubahan parameter sudah sangat kecil atau jumlah iterasi maksimum tercapai.
Untuk kasus dengan multiple input (regresi linear berganda), model bekerja dengan cara yang sama namun menggunakan persamaan yang lebih kompleks: y = b0 + b1x1 + b2x2 + ... + bnxn, di mana setiap x mewakili variabel input yang berbeda dan setiap b adalah koefisien yang perlu dipelajari model. Model tetap menggunakan prinsip yang sama untuk menemukan kombinasi koefisien optimal yang menghasilkan prediksi paling akurat.
Keakuratan model regresi linear dapat dievaluasi menggunakan berbagai metrik seperti Mean Squared Error (MSE), Root Mean Squared Error (RMSE), atau R-squared (R²). MSE mengukur rata-rata kuadrat error antara prediksi dan nilai aktual, RMSE adalah akar kuadrat dari MSE yang memberikan nilai error dalam unit yang sama dengan variabel target, sedangkan R² mengukur seberapa baik model menjelaskan variasi dalam data target, dengan nilai berkisar antara 0 hingga 1.
Meskipun sederhana, regresi linear menjadi fondasi penting dalam machine learning dan sering digunakan sebagai baseline model atau untuk kasus-kasus di mana hubungan antara variabel bersifat linear. Model ini juga memiliki keunggulan dalam hal interpretabilitas, di mana koefisien model dapat langsung diinterpretasikan sebagai besarnya pengaruh masing-masing variabel input terhadap output.
"""

def hybrid_summary(text):
    stopw = set(stopwords.words("indonesian"))
    words = word_tokenize(text)
    
    #menghitung term frequency (TF)
    tf = defaultdict(int)
    for word in words:
        word = word.lower()
        if word not in stopw:
            tf[word] += 1
    
    #menghitung document frequency (df) dan invers document frequency ()
    sentences = sent_tokenize(text)
    total_sentences = len(sentences)
    
    sentence_count = defaultdict(int)
    for sentence in sentences:
        unique_words = set(word_tokenize(sentence.lower()))
        for word in unique_words:
            if word in tf:
                sentence_count[word] += 1
    
    idf = {}
    for word in tf:
        idf[word] = math.log(total_sentences / (1 + sentence_count[word]))
    
    # Step 3: Compute Hybrid TF-IDF for Sentences
    sentence_scores = defaultdict(float)
    for sentence in sentences:
        for word in word_tokenize(sentence.lower()):
            if word in tf and word in idf:
                sentence_scores[sentence] += tf[word] * idf[word]
    
    # Step 4: Rank Sentences
    average_score = sum(sentence_scores.values()) / len(sentence_scores)
    summary = " ".join([
        sentence for sentence in sentences if sentence_scores[sentence] > 1.2 * average_score
        ])
    
    print(tf)
    print("")
    print(sentences)
    print("")
    print(idf)
    print("")
    print(sentence_scores)
    print("")
    print("Summary:")
    print(summary)

hybrid_summary(text)

