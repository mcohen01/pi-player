library('dplyr')
library('ggplot2')
library(grid)

filter.by <- function (df, filter.string) {
  correct <- tbl_df(df) %>%
               filter(is.correct == filter.string) %>%
               group_by(frame) %>%  
               summarise(length(frame))
  names(correct) <- c('frame', filter.string)
  return(correct)
}

uri <- "http://www.scienceofbehavior.com/enjoylife/Enjoy_Life_Set_Two.out"
lines <- read.csv("~/Sites/Enjoy_Life_Set_Two.out", stringsAsFactors=F, header=F)
lines <- read.csv(url(uri))

names(lines) <- c('name', 'tutorial', 'tries', 'frame', 'correct.answer', 'student.answer', 'is.correct', '8', '9', '10','11','12')

df <- data.frame(lines)
frame.scores <- merge(filter.by(df, 'CORRECT'), 
                      filter.by(df, 'INCORRECT'), 
                      all.x = T, 
                      all.y = T)
frame.scores <- data.frame(frame.scores)
# remove NAs
frame.scores[is.na(frame.scores$INCORRECT), 3] <- 0
#calcuate actual scores
frame.scores$score <- frame.scores$CORRECT / (frame.scores$CORRECT + frame.scores$INCORRECT)
frame.scores$low.score <- frame.scores$score < .75
# plot it

ggplot(frame.scores, aes(x = frame, y = score, fill=low.score)) + 
  geom_histogram(stat = "identity") + 
  scale_y_continuous(expand = c(0, 0)) +
  scale_x_continuous(breaks = 1:length(frame.scores[,3])) +
  scale_fill_manual(values=c('steelblue4', 'brown2')) +
  ggtitle("Frame Analysis") + 
  theme(plot.title = element_text(lineheight=.9)) +
  xlab("Frame Number") + 
  ylab("Score") + 
  theme(panel.grid.minor=element_blank(), panel.grid.major=element_blank()) +
  theme(legend.position = "none")

mean(frame.scores$score)

correct.answers <- tbl_df(df) %>%
                     select(frame, correct.answer) %>%
                     unique() %>%
                     arrange(frame) %>%
                     as.data.frame()

bad.frames <- frame.scores[frame.scores$score < .75,c(1)]
names(bad.frames) <- bad.frames


correct.answers[correct.answers$frame == 25,2]

tbl_df(df) %>%
  filter(is.correct == 'INCORRECT', frame == 34) %>%
  select(student.answer) %>%
  arrange(student.answer) %>%
  rename("Incorrect Responses" = student.answer)
